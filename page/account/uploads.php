<?php
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Upload\UploadManager;

function go(DocumentBinder $binder, UploadManager $uploadManager, User $user, Response $response):void {
	$uploadCount = $binder->bindList($uploadManager->getUploadsForUser($user));
	if($uploadCount === 0) {
		$response->redirect("/");
	}
	$binder->bindKeyValue("expiryDateString", $uploadManager->getExpiry($user)->format("jS M Y @ h:i a"));
}

function do_delete(Input $input, UploadManager $uploadManager, User $user, Response $response):void {
	$uploadManager->deleteById($user, $input->getString("id"));
	$response->reload();
}

function do_extend(UploadManager $uploadManager, User $user, Response $response):void {
	$uploadManager->extendExpiry($user);
}

function do_clear(UploadManager $uploadManager, User $user, Response $response):void {
	$uploadManager->clearUserFiles($user);
	$response->reload();
}
