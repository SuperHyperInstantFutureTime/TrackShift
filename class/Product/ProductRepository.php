<?php
namespace SHIFT\Trackshift\Product;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Royalty\Money;

readonly class ProductRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private ArtistRepository $artistRepository,
	) {
		parent::__construct($db);
	}

	/** @return array<ProductEarning> */
	public function getProductEarnings(User $user):array {
		$earningList = [];

		foreach($this->db->fetchAll("getEarnings", $user->id) as $row) {
			$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
			$product = new Product($row->getString("productId"), $row->getString("title"), $artist);
			$earning = new Money($row->getFloat("totalEarning"));
			$cost = new Money(0);
			if($costValue = $row->getFloat("totalCost")) {
				$cost = new Money($costValue);
			}

			array_push(
				$earningList,
				new ProductEarning(
					$user,
					$product,
					$earning,
					$cost,
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

	public function getForArtist(string|Artist $artist):array {
		$artist = is_string($artist) ? $this->artistRepository->getById($artist) : $artist;

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
