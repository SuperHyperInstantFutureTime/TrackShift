<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;

function go(
	Response $response,
	Authenticator $authenticator,
	UserMerger $userMerger,
	UserRepository $userRepository,
	?User $user,
):void {
	if($authenticator->isLoggedIn()) {
		$authwaveUser = $authenticator->getUser();
		$currentUser = $user;
		if($existingUser = $userRepository->findByAuthwaveId($authwaveUser->id)) {
// TODO: Move all of $user's stuff to $existingUser's.
			$currentUser = $existingUser;
		}

		$userRepository->persistUser($currentUser);
	}
	else {
		$authenticator->login();
	}

	$response->redirect("/account/");
}
