<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;

function go(
	Input $input,
	Response $response,
	Authenticator $authenticator,
	UserRepository $userRepository,
	User $user,
	Uri $uri,
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
		if($debug = $input->getString("debug")) {
			$authenticator->fakeLogin($debug, $uri->getPath());
		}
		else {
			$authenticator->login();
		}
	}

	$response->redirect("/account/");
}

function do_cancel(Response $response):void {
	$response->redirect("/");
}

function do_logout(Response $response):void {
	$response->redirect("/auth/logout/");
}
