<?php
namespace SHIFT\TrackShift\Product;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Logger\Log;
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\FilterQuery;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\NormalisedString;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\UsageRepository;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
readonly class ProductRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private ArtistRepository $artistRepository,
	) {
		parent::__construct($db);
	}

	public function create(User $user, Product...$productsToCreate):int {
		$count = 0;
		foreach($productsToCreate as $product) {
			$this->db->insert("create", [
				"id" => $product->id,
				"artistId" => $product->artist->id,
				"title" => $product->title,
				"titleNormalised" => new NormalisedString($product->title),
				"uploadUserId" => $user->id,
			]);
			$count ++;
		}

		return $count;
	}

	public function find(string $productTitle, Artist $artist, bool $normalisedTitle = false):?Product {
		$all = $this->db->fetchAll("getAll")->asArray();
		$queryName = $normalisedTitle ? "getProductByTitleNormalisedAndArtist" : "getProductByTitleAndArtist";
		return $this->rowToProduct($this->db->fetch($queryName, [
			"title" => $productTitle,
			"artistId" => $artist->id,
		]), $artist);
	}

	public function lookupMissingTitles(SpotifyClient $spotify):int {
		set_time_limit(0);
		$count = 0;

// Convert this to work with ISRCs too. If there's no UPC, look it up via the ISRC.

		foreach($this->db->fetchAll("getAllMissingTitles") as $row) {
			$product = $this->rowToProduct($row);
			$upc = substr($product->title, strlen(UsageRepository::UNSORTED_UPC));

			$cacheFile = "data/cache/upc/$upc.dat";
			$album = null;
			if(is_file($cacheFile)) {
				Log::debug("Using cache for UPC: $upc");
				$album = unserialize(file_get_contents($cacheFile));
			}
			else {
				Log::debug("Looking up UPC: $upc");

				$result = $spotify->search->query(
					new FilterQuery(upc: $upc),
					new SearchFilter(EntityType::album),
				);
				$albumSearch = $result->albums->items[0] ?? null;
				if($albumId = $albumSearch?->id) {
					Log::debug("Found ID: $albumId");
					$album = $spotify->albums->get($albumId);
				}
				else {
					Log::debug("Not found!");
				}

				if(!is_dir(dirname($cacheFile))) {
					mkdir(dirname($cacheFile), recursive: true);
				}
				file_put_contents($cacheFile, serialize($album));
			}

			if($album) {
				$count += $this->db->update("setProductTitle", [
					"id" => $product->id,
					"title" => $album->name,
					"titleNormalised" => new NormalisedString($album->name),
				]);
			}
		}

		return $count;
	}

	public function changeProductIsrcToUpc(string $isrc, string $upc):void {
		$this->db->update("rename", [
			"oldTitle" => UsageRepository::UNSORTED_ISRC . $isrc,
			"newTitle" => UsageRepository::UNSORTED_UPC . $upc,
		]);
	}

	/** @return array<ProductEarning> */
	public function getProductEarnings(User $user, int $count, int $offset):array {
		$earningList = [];

		$resultSet = $this->db->fetchAll("getEarnings", [
			"userId" => $user->id,
			"limit" => $count,
			"offset" => $offset,
		]);
		foreach($resultSet as $row) {
			$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
			$earning = new Money(0.00);
			if($totalEarningFloat = $row->getFloat("totalEarningCache")) {
				$earning = new Money($totalEarningFloat);
			}
			$product = new Product(
				$row->getString("productId"),
				$row->getString("title"),
				$artist,
				$earning,
			);

			$cost = new Money();
			if($costValue = $row->getFloat("totalCost")) {
				$cost = new Money($costValue);
			}

			$balance = $earning->withSubtraction($cost);

			$outgoing = new Money();
			if($outgoingPercentage = $row->getFloat("percentageOutgoing")) {
				$outgoingValue = ($outgoingPercentage / 100) * $balance->value;
				$outgoing = new Money(round($outgoingValue, 2));
			}

			$profit = $balance->withSubtraction($outgoing);

			array_push(
				$earningList,
				new ProductEarning(
					$user,
					$product,
					$earning,
					$cost,
					$outgoing,
					$profit,
				)
			);
		}

		return $earningList;
	}

	public function getById(string $id):Product {
		$row = $this->db->fetch("getById", $id);
		$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
		return new Product(
			$row->getString("id"),
			$row->getString("title"),
			$artist,
		);
	}

	/** @return array<Product> */
	public function getForArtist(string|Artist $artist, User $user):array {
		$artist = is_string($artist) ? $this->artistRepository->getById($artist, $user) : $artist;

		$productArray = [];
		foreach($this->db->fetchAll("getAllByArtistId", $artist->id) as $row) {
			array_push(
				$productArray,
				$this->rowToProduct($row, $artist),
			);
		}
		return $productArray;
	}

	public function getByNormalisedTitleAndArtist(string $productTitleNormalised, Artist $artist, User $user):?Product {
		return $this->rowToProduct($this->db->fetch("getByNormalisedTitleAndArtist", [
			"normalisedTitle" => $productTitleNormalised,
			"artistId" => $artist->id,
			"userId" => $user->id,
		]), $artist);
	}


	public function calculateUncachedEarnings():void {
		foreach($this->db->fetchAll("calculateUncachedEarnings") as $i => $row) {
			$product = $this->rowToProduct($row);
			Log::debug("$i\t" . $product->title ." cached earning: " . $product->totalEarning->value);
			$this->db->update("storeCachedEarning", [
				"productId" => $product->id,
				"earning" => $product->totalEarning->value,
			]);
		}
	}

	public function clearEarningCache(mixed $product):void {
		$this->db->update("clearProductEarningCache", $product->id);
	}

	public function getSummary(User $user):ProductSummary {
		$earnings = $this->db->fetchFloat("getSummaryEarnings", [
			"userId" => $user->id,
		]);
		$costs = $this->db->fetchFloat("getSummaryCosts", [
			"userId" => $user->id,
		]);
		return new ProductSummary($earnings ?? 0.0, $costs ?? 0.0);
	}

	private function rowToProduct(?Row $row, ?Artist $artist = null):?Product {
		if(!$row) {
			return null;
		}

		$totalEarning = new Money();
		if($totalEarningFloat = $row->getFloat("totalEarningCache")) {
			$totalEarning = new Money($totalEarningFloat);
		}
		return new Product(
			$row->getString("id"),
			$row->getString("title"),
			$artist,
			$totalEarning,
		);
	}

	public function getAll():array {
		$all = [];
		foreach($this->db->fetchAll("getAll") as $row) {
			array_push($all, $this->rowToProduct($row));
		}
		return $all;
	}



}
