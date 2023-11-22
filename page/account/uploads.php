<?php
use Authwave\Authenticator;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Upload\UploadRepository;

function go(Binder $binder, UploadRepository $uploadRepository, User $user, Response $response):void {
	$binder->bindList($uploadRepository->getUploadsForUser($user));
}

function do_delete(Input $input, UploadRepository $uploadRepository, User $user, Response $response):void {
	$uploadRepository->deleteById($user, $input->getString("id"));
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
