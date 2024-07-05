<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	UserRepository $userRepository,
	UploadRepository $uploadRepository,
	Authenticator $authenticator,
	User $user,
	Input $input,
	Response $response,
):void {
	if($uploadRepository->getUploadsForUser($user)) {
		if(!$input->contains("homepage")) {
			$response->redirect("/account/usingTrackshift/");
		}
	}

	if($input->contains("debug-user")) {
		if($authenticator->isLoggedIn()) {
			$authwaveUser = $authenticator->getUser();
			$matchingDebugUser = $userRepository->findByAuthwaveId($authwaveUser->id);
			if($matchingDebugUser) {
				$userRepository->persistUser($matchingDebugUser);
			}
			else {
				$userRepository->associateAuthwave($user, $authwaveUser);
			}
			$response->redirect("/");
		}
	}

	if($input->contains("debug-user")) {
		if($authenticator->isLoggedIn()) {
			$authwaveUser = $authenticator->getUser();
			$matchingDebugUser = $userRepository->findByAuthwaveId($authwaveUser->id);
			if($matchingDebugUser) {
				$userRepository->persistUser($matchingDebugUser);
			}
			else {
				$userRepository->associateAuthwave($user, $authwaveUser);
			}
			$response->redirect("/");
		}
	}
}
