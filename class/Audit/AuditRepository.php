<?php
namespace SHIFT\Trackshift\Audit;

use DateTime;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Repository\Repository;

readonly class AuditRepository extends Repository {
	public function __construct(
		QueryCollection $db,
		private UserRepository $userRepository,
	) {
		parent::__construct($db);
	}

	public function create(User $user, string $newId, ?string $description = null):void {
		$this->db->insert("insertCreation", [
			"id" => new Ulid("audit"),
			"userId" => $user->id,
			"valueId" => $newId,
			"description" => $description,
		]);
	}

	public function notify(User $user, string $description, ?string $idInQuestion = null):void {
		$this->db->insert("insertNotification", [
			"id" => new Ulid("audit"),
			"userId" => $user->id,
			"description" => $description,
			"valueId" => $idInQuestion,
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

	public function getLatest(User $user):null|AuditItem|NotificationItem {
		$row = $this->db->fetch("getLatest", $user->id);
		return $this->rowToAuditItem($row, $user);
	}

	private function rowToAuditItem(?Row $row, ?User $user = null):?AuditItem {
		if(!$row) {
			return null;
		}

		if(!$user) {
			$user = $this->userRepository->getById($row->getString("userId"));
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

	public function checkNotifications(User $user):void {
		$this->userRepository->setNotificationCheckTime($user);
	}

	public function isNewNotification(User $user):bool {
		$timeLatest = new DateTime();
		$timeChecked = $this->userRepository->getLatestNotificationCheckTime($user);

		if($latestNotification = $this->getLatest($user)) {
			$timeLatest->setTimestamp((new Ulid(init: $latestNotification->id))->getTimestamp() / 1000);
		}

		return $timeLatest > $timeChecked;
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
