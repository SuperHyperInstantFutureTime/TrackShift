<?php
namespace SHIFT\Trackshift\Cost;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Royalty\Money;

readonly class CostRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private ProductRepository $productRepository,
	) {
		parent::__construct($db);
	}

	public function create(Cost $cost):void {
		$this->db->insert("create", [
			"id" => $cost->id,
			"productId" => $cost->product->id,
			"description" => $cost->description,
			"amount" => $cost->amount->value,
		]);
	}

	public function update(Cost $cost):void {
		$this->db->update("update", [
			"id" => $cost->id,
			"productId" => $cost->product->id,
			"description" => $cost->description,
			"amount" => $cost->amount->value,
		]);
	}


	public function delete(Cost|string $cost):void {
		$costId = is_string($cost) ? $cost : $cost->id;
		$this->db->delete("delete", $costId);
	}


	public function getById(string $id):?Cost {
		return $this->rowToCost($this->db->fetch("getById", $id));
	}

	/** @return array<Cost> */
	public function getAll(User $user):array {
		$costArray = [];

		foreach($this->db->fetchAll("getAllForUser", $user->id) as $row) {
			array_push(
				$costArray,
				$this->rowToCost($row),
			);
		}

		return $costArray;
	}

	private function rowToCost(?Row $row):?Cost {
		if(!$row) {
			return null;
		}

		$product = $this->productRepository->getById($row->getString("productId"));

		return new Cost(
			$row->getString("id"),
			$product,
			$row->getString("description"),
			new Money($row->getFloat("amount")),
		);
	}
}
