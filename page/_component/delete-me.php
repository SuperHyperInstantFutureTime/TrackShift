<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Upload\UploadRepository;

function do_clear(
	UploadRepository $uploadRepository,
	UserRepository $userRepository,
	User $user,
	Authenticator $authenticator,
	Response $response,
):void {
	$uploadRepository->clearUserData($user);
	$userRepository->forget($user);
	if($authenticator->isLoggedIn()) {
		$authenticator->logout();
	}

	$response->redirect("/");
}
