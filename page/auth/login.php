<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;

function go(
	Response $response,
	Authenticator $authenticator,
	UserRepository $userRepository,
	User $user,
):void {
	if($authenticator->isLoggedIn()) {
		$authwaveUser = $authenticator->getUser();
		if($existingDbUser = $userRepository->findByAuthwaveId($authwaveUser->id)) {
			$user = $existingDbUser;
		}
		else {
			$userRepository->associateAuthwave($user, $authenticator->getUser());
		}

		$userRepository->persistUser($user);
	}
	else {
		$authenticator->login();
	}

	$response->redirect("/account/");
}
