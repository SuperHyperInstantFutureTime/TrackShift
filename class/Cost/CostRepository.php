<?php
namespace SHIFT\TrackShift\Cost;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use SHIFT\TrackShift\Audit\AuditRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Money;

readonly class CostRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private ProductRepository $productRepository,
//		private AuditRepository $auditRepository,
	) {
		parent::__construct($db);
	}

	public function create(Cost $cost, User $user):void {
//		$this->auditRepository->create(
//			$user,
//			$cost->id,
//			$cost->product->title . " " . $cost->description . " " . $cost->amount
//		);
		$this->db->insert("create", [
			"id" => $cost->id,
			"productId" => $cost->product->id,
			"userId" => $user->id,
			"description" => $cost->description,
			"amount" => $cost->amount->value,
			"date" => $cost->date,
		]);
	}

	public function update(Cost $cost, User $user):void {
//		$oldCost = $this->getById($cost->id);

//		$this->auditRepository->update(
//			$user,
//			$cost->id,
//			$oldCost,
//			$cost
//		);

		$this->db->update("update", [
			"id" => $cost->id,
			"productId" => $cost->product->id,
			"description" => $cost->description,
			"amount" => $cost->amount->value,
			"date" => $cost->date,
			"userId" => $user->id
		]);
	}


	public function delete(Cost|string $cost, User $user):void {
		$cost = is_string($cost) ? $this->getById($cost) : $cost;

//		$this->auditRepository->delete(
//			$user,
//			$cost->id,
//			trim($cost->product->title . " " . $cost->description . " " . $cost->amount)
//		);
		$this->db->delete("delete", [
			"id" => $cost->id,
			"userId" => $user->id,
		]);
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
			$row->getDateTime("date"),
		);
	}
}
