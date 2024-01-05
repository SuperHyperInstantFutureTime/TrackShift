<?php
namespace SHIFT\TrackShift\Split;

use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\Repository;

readonly class SplitRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private UserRepository $userRepository,
		private ProductRepository $productRepository,
	) {
		parent::__construct($db);
	}

	/** @return array<Split> */
	public function getAll(User $user, ?string $remainderName = null):array {
		$splitList = [];

		$resultSet = $this->db->fetchAll("getAll", $user->id);
		foreach($resultSet as $row) {
			array_push(
				$splitList,
				$this->rowToSplit($row, $user, remainderName: $remainderName),
			);
		}

		return $splitList;
	}

	/** @return array<SplitPercentage> */
	public function getSplitPercentageList(User $user, string $splitId, ?string $remainderName = null):array {
		$resultSet = $this->db->fetchAll("getSplitPercentageList", $splitId, $user->id);

		$splitPercentageList = [];
		foreach($resultSet as $row) {
			array_push(
				$splitPercentageList,
				$this->rowToSplitPercentage($row),
			);
		}

		if($remainderName) {
			array_push($splitPercentageList, new RemainderSplitPercentage($splitPercentageList, $remainderName));
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

	public function addSplitPercentage(Split $split, SplitPercentage $splitPercentage):void {
		$this->db->insert("addSplitPercentage", [
			"id" => $splitPercentage->id,
			"splitId" =>  $split->id,
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

	private function rowToSplit(?Row $row, ?User $user = null, ?string $remainderName = null):?Split {
		if(!$row) {
			return null;
		}

		$id = $row->getString("id");
		$user = $user ?? $this->userRepository->getById($row->getString("userId"));
		$product = $this->productRepository->getById($row->getString("productId"));

		$splitPercentageList = $this->getSplitPercentageList($user, $id, $remainderName);

		return new Split(
			$id,
			$user,
			$product,
			$splitPercentageList,
		);
	}

	private function rowToSplitPercentage(?Row $row):?SplitPercentage {
		if(!$row) {
			return null;
		}

		$id = $row->getString("id");

		return new SplitPercentage(
			$id,
			$row->getString("owner"),
			$row->getFloat("percentage"),
			$row->getString("contact"),
		);
	}
}
