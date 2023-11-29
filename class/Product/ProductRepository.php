<?php
namespace SHIFT\TrackShift\Product;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Money;

readonly class ProductRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private ArtistRepository $artistRepository,
	) {
		parent::__construct($db);
	}

	public function create(Product...$productsToCreate):int {
		$count = 0;
		foreach($productsToCreate as $product) {
			$count += $this->db->insert("create", [
				"id" => $product->id,
				"artistId" => $product->artist->id,
				"title" => $product->title,
			]);
		}

		return $count;
	}

	public function find(string $productTitle, Artist $artist):?Product {
		return $this->rowToProduct($this->db->fetch("getProductByTitleAndArtist", [
			"title" => $productTitle,
			"artistId" => $artist->id,
		]), $artist);
	}


	/** @return array<ProductEarning> */
	public function getProductEarnings(User $user):array {
		$earningList = [];

		foreach($this->db->fetchAll("getEarnings", $user->id) as $row) {
			$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
			$product = new Product($row->getString("productId"), $row->getString("title"), $artist);
			$earning = new Money($row->getFloat("totalEarning"));
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

		$artistArray = [];
		foreach($this->db->fetchAll("getAllByArtistId", $artist->id) as $row) {
			array_push(
				$artistArray,
				$this->rowToProduct($row, $artist),
			);
		}
		return $artistArray;
	}

	private function rowToProduct(?Row $row, ?Artist $artist):?Product {
		if(!$row) {
			return null;
		}

		return new Product(
			$row->getString("id"),
			$row->getString("title"),
			$artist,
		);
	}
}
