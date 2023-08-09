<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Egg\UploadMessageList;
use SHIFT\Trackshift\Upload\UploadManager;

function go(Response $response):void {
	$response->redirect("/account/uploads/");
}

function do_upload(
	Input $input,
	Response $response,
	UserRepository $userRepository,
	User $user,
	UploadManager $uploadManager,
):void {
	$userRepository->persistUser($user);

	$fileNameList = $uploadManager->upload($user, ...$input->getMultipleFile("upload"));
	$productList = $uploadManager->processUploads($user, ...$fileNameList);
//	$uploadManager->cacheArt($spotify, ...$productList);

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}

function do_delete(Input $input, Response $response, User $user, UploadManager $uploadManager):void {
	$uploadManager->delete($user, $input->getString("filename"));
	$response->reload();
}

function do_clear(User $user, UploadManager $uploadManager, Response $response):void {
	$uploadManager->clearUserFiles($user);
	$response->reload();
}

function do_extend(User $user, UploadManager $uploadManager, Response $response):void {
	$uploadManager->extendExpiry($user);
	$response->reload();
}
