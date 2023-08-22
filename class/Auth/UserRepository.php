<?php
namespace SHIFT\Trackshift\Auth;

use Gt\Database\Query\QueryCollection;
use Gt\Session\SessionStore;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Repository\Repository;

readonly class UserRepository extends Repository {
	const SESSION_STORE_KEY = "trackshift-auth-store";
	const SESSION_USER = "user";

	public function __construct(
		QueryCollection $db,
		private SessionStore $session,
	) {
		parent::__construct($db);
	}

	public function getLoggedInUser():?User {
		return $this->session->getInstance(self::SESSION_USER, User::class);
	}

	public function createNewUser():User {
		$user = new User(new Ulid());
		$this->uploadDb->insert("create", $user->id);
		return $user;
	}

	public function persistUser(User $user):void {
		$this->session->set(self::SESSION_USER, $user);
	}
}
