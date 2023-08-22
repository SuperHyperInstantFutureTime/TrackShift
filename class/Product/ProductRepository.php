<?php
namespace SHIFT\Trackshift\Product;

use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Royalty\Money;

readonly class ProductRepository extends Repository {
	/** @return array<ProductEarning> */
	public function getProductEarnings(User $user):array {
		$earningList = [];

		foreach($this->uploadDb->fetchAll("getEarnings", $user->id) as $row) {
			$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
			$product = new Product($row->getString("productId"), $row->getString("title"), $artist);
			$earning = new Money($row->getFloat("totalEarning"));

			array_push(
				$earningList,
				new ProductEarning(
					$user,
					$product,
					$earning,
				)
			);
		}

		return $earningList;
	}

	public function getById(string $id):Product {
		$row = $this->uploadDb->fetch("getById", $id);
		$artist = new Artist($row->getString("artistId"), $row->getString("artistName"));
		return new Product(
			$row->getString("id"),
			$row->getString("title"),
			$artist,
		);
	}

}
