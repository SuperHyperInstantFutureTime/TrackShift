<?php
namespace SHIFT\TrackShift\Usage;

use Gt\Database\Query\QueryCollection;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Upload\Upload;

readonly class UsageRepository extends Repository {
	const UPC_SYNTAX = "::UPC::";
	const UNKNOWN_UPC_SYNTAX = "::UNKNOWN_UPC::";

	public function __construct(
		QueryCollection $db,
	) {
		parent::__construct($db);
	}

	/** @return array<Usage> */
	public function createUsagesFromUpload(Upload $upload):array {
		$usageList = [];

		foreach($upload->generateDataRows() as $row) {
			$usage = new Usage(
				new Ulid("usage"),
				$upload,
				$row,
			);
			array_push($usageList, $usage);
			$this->db->insert("create", [
				"id" => $usage->id,
				"uploadId" => $upload->id,
				"data" => json_encode($row),
			]);
		}

		return $usageList;
	}

	/** @param array<Usage> $usageList */
	public function process(
		User $user,
		array $usageList,
		Upload $upload,
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
	):int {
		$importedUsageIdList = [];
		$importedArtistNameList = [];
		$importedProductTitleList = [];
		$importedCombinedArtistNameProductTitleList = [];
		$importedEarningList = [];
		$mapCombinedArtistNameProductTitleToProduct = [];

		$artistList = [];

		foreach($usageList as $usage) {
			$artistName = $upload->extractArtistName($usage->row);
			$productTitle = $upload->extractProductTitle($usage->row);
			$earning = $upload->extractEarning($usage->row);

			array_push($importedUsageIdList, $usage->id);
			array_push($importedArtistNameList, $artistName);
			array_push($importedProductTitleList, $productTitle);
			array_push($importedEarningList, $earning);

			array_push($importedCombinedArtistNameProductTitleList, $artistName . "__" . $productTitle);
			$this->db->update("setProcessed", $usage->id);
		}

		$importedUniqueArtistNameList = array_unique($importedArtistNameList);
		/** @var array<Artist> $toCreateArtistList */
		$toCreateArtistList = [];
		$mapArtistNameToId = [];
		foreach($importedUniqueArtistNameList as $artistName) {
			$artist = $artistRepository->getByName($artistName, $user);
			if(!$artist) {
				$artist = new Artist(
					new Ulid("artist"),
					$artistName,
				);
				array_push($toCreateArtistList, $artist);
			}

			$artistList[$artist->id] = $artist;
			$mapArtistNameToId[$artistName] = $artist->id;
		}

/* At this point, there may be missing product titles, due to statement types
such as DistroKid supplying track names instead. The product title may be
encoded as a UPC code, in which case we can figure it out ourselves... */
		foreach($importedProductTitleList as $i => $productTitle) {
			if(str_starts_with($productTitle, "::")) {
				preg_match("/(::UPC::(?P<UPC>.+)|::UNKNOWN_UPC::(?P<ISRC>.+))/", $productTitle, $matches);
				$foundUpc = null;
				if($isrc = $matches["ISRC"] ?? null) {
					$foundUpc = $upload->isrcUpcMap[$isrc] ?? null;
				}

				if($upc = $matches["UPC"] ?: $foundUpc ?? null) {
					if($foundTitle = $upload->upcProductTitleMap[$upc] ?? null) {
						$importedProductTitleList[$i] = $foundTitle;
						$importedCombinedArtistNameProductTitleList[$i] = $importedArtistNameList[$i] . "__" . $foundTitle;
					}
					else {
						$foundTitle = self::UPC_SYNTAX . $upc;
						$importedProductTitleList[$i] = $foundTitle;
						$importedCombinedArtistNameProductTitleList[$i] = $importedArtistNameList[$i] . "__" . $foundTitle;
					}
				}
			}
		}

		$importedUniqueCombinedArtistNameProductTitleList = array_unique($importedCombinedArtistNameProductTitleList);
		/** @var array<Product> $toCreateProductList */
		$toCreateProductList = [];
		foreach($importedUniqueCombinedArtistNameProductTitleList as $combinedArtistProduct) {
			[$artistName, $productTitle] = explode("__", $combinedArtistProduct);
			$artistId = $mapArtistNameToId[$artistName] ?? null;
			if(!$artistId) {
				continue;
			}

			$artist = $artistList[$artistId];

			$product = $productRepository->find($productTitle, $artist);
			if(!$product) {
				$product = new Product(
					new Ulid("product"),
					$productTitle,
					$artist,
				);
				array_push($toCreateProductList, $product);
			}
//			$productList[$product->id] = $product;
			$mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct] = $product;
		}

		$artistCount = $artistRepository->create($user, ...$toCreateArtistList);
		$productCount = $productRepository->create(...$toCreateProductList);

		Log::debug("Created $artistCount artists and $productCount products");

		$count = 0;
		foreach($importedEarningList as $i => $earning) {
			$artistName = $importedArtistNameList[$i];
			$productTitle = $importedProductTitleList[$i];
			$combinedArtistProduct = $artistName . "__" . $productTitle;
			$product = $mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct] ?? null;

			if(!$product) {
				continue;
			}

			$count += $this->db->insert("assignProductUsage", [
				"id" => (string)(new Ulid("pu")),
				"usageId" => $importedUsageIdList[$i],
				"productId" => $product->id,
				"earning" => $earning->value,
			]);
		}

		return $count;
	}
}
