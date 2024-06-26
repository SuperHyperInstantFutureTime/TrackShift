<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;

function go(
	UserRepository $userRepository,
	Authenticator $authenticator,
	Input $input,
	Response $response,
	User $user,
	Uri $uri,
):void {
	if($debugUser = $input->getString("debug-user")) {
		if(str_starts_with($uri->getHost(), "localhost")) {
			if(!$authenticator->isLoggedIn()) {
				$authenticator->fakeLogin($debugUser);
			}
		}
	}

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
	$response->redirect("/");
}
