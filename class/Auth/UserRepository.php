<?php
namespace SHIFT\Trackshift\Auth;

use Authwave\User as AuthwaveUser;
use DateTime;
use DateTimeInterface;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Session\SessionStore;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Repository\Repository;

readonly class UserRepository extends Repository {
	const SESSION_STORE_KEY = "trackshift-user-store";
	const SESSION_AUTHENTICATOR_STORE_KEY = "trackshift-authwave-store";
	const SESSION_USER = "trackshift-user";

	public function __construct(
		QueryCollection $db,
		private SessionStore $session,
	) {
		parent::__construct($db);
	}

	public function findByAuthwaveId(string $id):?User {
		return $this->rowToUser(
			$this->db->fetch("getByAuthwaveId", $id)
		);
	}

	public function getLoggedInUser():?User {
		return $this->session->getInstance(self::SESSION_USER, User::class);
	}

	public function getById(string $id):?User {
		$row = $this->db->fetch("getById", $id);
		return $this->rowToUser($row);
	}

	public function createNewUser():User {
		$user = new User(new Ulid("user"));
		$this->db->insert("create", $user->id);
		return $user;
	}

	public function persistUser(User $user):void {
		$this->session->set(self::SESSION_USER, $user);
	}

	public function forget():void {
		$this->session->remove(self::SESSION_USER);
	}

	public function setNotificationCheckTime(User $user, DateTime $when = null):void {
		if(is_null($when)) {
			$when = new DateTime();
		}

		$this->db->update("setNotificationCheckedAt", [
			"userId" => $user->id,
			"checkedAt" => $when->getTimestamp(),
		]);
	}

	public function getLatestNotificationCheckTime(User $user):?DateTimeInterface {
		return $this->db->fetchDateTime("getLastNotificationCheckTime", $user->id);
	}

	private function rowToUser(?Row $row):?User {
		if(!$row) {
			return null;
		}

		return new User($row->getString("id"));
	}

	public function associateAuthwave(User $user, AuthwaveUser $authwaveUser):void {
		$this->db->update("associateAuthwave", [
			"userId" => $user->id,
			"authwaveId" => $authwaveUser->id,
		]);
	}


}
