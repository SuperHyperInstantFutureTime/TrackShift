<?php
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Upload\UploadManager;

function go(DocumentBinder $binder, UploadManager $uploadManager, User $user):void {
	$binder->bindList($uploadManager->getUploadsForUser($user));
}

function do_delete(Input $input, UploadManager $uploadManager, User $user, Response $response):void {
	$uploadManager->delete($user, $input->getString("id"));
	$response->reload();
}
