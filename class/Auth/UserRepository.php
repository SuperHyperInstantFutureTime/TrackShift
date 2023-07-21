<?php
namespace SHIFT\Trackshift\Auth;

use Gt\Session\SessionStore;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Repository\Repository;

readonly class UserRepository extends Repository {
	const SESSION_STORE_KEY = "trackshift-auth-store";
	const SESSION_USER = "user";

	public function __construct(
		private SessionStore $session,
	) {}

	public function getLoggedInUser():?User {
		return $this->session->getInstance(self::SESSION_USER, User::class);
	}

	public function createNewUser():User {
		$user = new User(new Ulid());
		$this->session->set(self::SESSION_USER, $user);
		return $user;
	}
}
