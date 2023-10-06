<?php
namespace SHIFT\Trackshift\Audit;

use Gt\Database\Result\Row;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;

readonly class AuditRepository extends Repository {
	public function create(User $user, string $newId, ?string $description = null):void {
		$this->db->insert("insertCreation", [
			"id" => new Ulid("audit"),
			"userId" => $user->id,
			"valueId" => $newId,
			"description" => $description,
		]);
	}

	public function delete(User $user, string $deletedId, ?string $description = null):void {
		$this->db->insert("insertDeletion", [
			"id" => new Ulid("audit"),
			"userId" => $user->id,
			"valueId" => $deletedId,
			"description" => $description,
		]);
	}

	/** @return array<AuditItem,NotificationItem> */
	public function getAll(User $user):array {
		$auditItemArray = [];

		foreach($this->db->fetchAll("getAll", $user->id) as $row) {
			array_push(
				$auditItemArray,
				$this->rowToAuditItem($row, $user),
			);
		}

		return $auditItemArray;
	}

	private function rowToAuditItem(?Row $row, ?User $user = null):?AuditItem {
		if(!$row) {
			return null;
		}

		return new AuditItem(
			$row->getString("id"),
			$user,
			$row->getBool("isNotification"),
			$row->getString("type"),
			$row->getString("description"),
			$row->getString("valueId"),
			$row->getString("valueField"),
			$row->getString("valueFrom"),
			$row->getString("valueTo"),
		);
	}

	public function update(
		User $user,
		string $id,
		object $from,
		object $to,
		?string $description = null,
	):void {
		$diff = $this->getDiff($from, $to);

		foreach($diff as $field => $fromTo) {
			$fromToDescription = "$field: $fromTo";
			[$from, $to] = explode("->", $fromTo);
			if($description) {
				$fromToDescription .= " - $description";
			}

			$this->db->insert("insertUpdate", [
				"id" => new Ulid("audit"),
				"userId" => $user->id,
				"valueId" => $id,
				"valueField" => $field,
				"valueFrom" => $from,
				"valueTo" => $to,
				"description" => $fromToDescription,
			]);
		}
	}

	/** @return array<string, string> key = property name, value = "$oldValue -> $newValue" */
	private function getDiff(object $from, object $to):array {
		$fromVars = get_object_vars($from);
		$toVars = get_object_vars($to);

		$diff = [];

		foreach($fromVars as $key => $value) {
			if($toVars[$key] != $value) {
				$diff[$key] = "$value -> $toVars[$key]";
			}
		}

		return $diff;
	}


}
