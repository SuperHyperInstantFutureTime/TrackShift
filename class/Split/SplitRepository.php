<?php
namespace SHIFT\Trackshift\Split;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Repository\Repository;

readonly class SplitRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private UserRepository $userRepository,
		private ProductRepository $productRepository,
	) {
		parent::__construct($db);
	}

	/** @return array<SplitPercentage> */
	public function getSplitPercentageList(User $user, string $splitId):array {
		$resultSet = $this->db->fetchAll("getSplitPercentageList", $splitId, $user->id);

		$splitPercentageList = [];
		foreach($resultSet as $row) {
			array_push(
				$splitPercentageList,
				$this->rowToSplitPercentage($row, $user),
			);
		}
		return $splitPercentageList;
	}

	public function create(Product $product, User $user):Split {
		$split = new Split(
			new Ulid("split"),
			$user,
			$product,
		);
		$this->db->insert("create", [
			"id" => $split->id,
			"userId" => $split->user->id,
			"productId" => $split->product->id,
		]);

		return $split;
	}

	public function getById(string $id, User $user):Split {
		$row = $this->db->fetch("getById", [
			"id" => $id,
			"userId" => $user->id,
		]);
		return $this->rowToSplit($row, $user);
	}

	public function addSplitPercentage(SplitPercentage $splitPercentage):void {
		$this->db->insert("addSplitPercentage", [
			"id" => $splitPercentage->id,
			"splitId" =>  $splitPercentage->split->id,
			"owner" => $splitPercentage->owner,
			"percentage" => $splitPercentage->percentage,
			"contact" => $splitPercentage->contact,
		]);
	}

	public function deleteSplitPercentage(string $id):void {
		$this->db->delete("deleteSplitPercentage", $id);
	}


	public function delete(string $splitId, User $user):void {
		$this->db->delete("delete", $splitId, $user->id);
	}

	private function rowToSplit(?Row $row, ?User $user = null):?Split {
		if(!$row) {
			return null;
		}

		$user = $user ?? $this->userRepository->getById($row->getString("userId"));
		$product = $this->productRepository->getById($row->getString("productId"));

		return new Split(
			$row->getString("id"),
			$user,
			$product,
		);
	}

	private function rowToSplitPercentage(?Row $row, ?User $user = null):?SplitPercentage {
		if(!$row) {
			return null;
		}
		if(!$user) {
			$user = $this->userRepository->getById($row->getString("userId"));
		}

		return new SplitPercentage(
			$row->getString("id"),
			$this->getById($row->getString("splitId"), $user),
			$row->getString("owner"),
			$row->getFloat("percentage"),
			$row->getString("contact"),
		);
	}

}
