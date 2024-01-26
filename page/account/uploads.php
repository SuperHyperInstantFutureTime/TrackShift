<?php
use Authwave\Authenticator;
use Gt\Database\Database;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(Binder $binder, UploadRepository $uploadRepository, User $user, Response $response):void {
	$binder->bindList($uploadRepository->getUploadsForUser($user));
}

function do_delete(
	Input $input,
	UploadRepository $uploadRepository,
	UsageRepository $usageRepository,
	User $user,
	Response $response,
	Database $database,
):void {
	$upload = $uploadRepository->getById($input->getString("id"), $user);
	$uploadRepository->delete($upload, $user);
	$response->reload();
}

function do_clear(
	Response $response,
	UploadRepository $uploadRepository,
	UserRepository $userRepository,
	Authenticator $authenticator,
	User $user,
):void {
	$uploadRepository->clearUserData($user);
	$userRepository->forget();
	if($authenticator->isLoggedIn()) {
		$authenticator->logout();
	}

	$response->redirect("/");
}
