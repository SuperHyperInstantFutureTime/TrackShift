<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Upload\UploadManager;

function go(
	HTMLDocument $document,
	Input $input,
	DocumentBinder $binder,
	User $user,
	UploadManager $uploadManager,
):void {
	if($advance = $input->getString("advance")) {
		$binder->bindKeyValue("advance", $advance);
		$binder->bindKeyValue("advance-auto", $advance === "auto");
	}

	$uploadCount = $binder->bindList($uploadManager->getUploadsForUser($user), $document->querySelector("file-upload-list"));
	$binder->bindKeyValue("uploadCount", $uploadCount);
	$binder->bindKeyValue("expiryDateString", $uploadManager->getExpiry($user)->format("dS M Y"));
}

function do_upload(Input $input, Response $response, User $user, UploadManager $uploadManager):void {
	$uploadManager->upload($user, ...$input->getMultipleFile("upload"));
	$response->reload();
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
