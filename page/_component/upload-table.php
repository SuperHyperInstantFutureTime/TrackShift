<?php
use Gt\Database\Database;
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(
	UploadRepository $uploadRepository,
	User $user,
	Element $element,
	Binder $binder,
):void {
	$binder->bindList($uploadRepository->getUploadsForUser($user));
}

function do_delete(
	UploadRepository $uploadRepository,
	ProductRepository $productRepository,
	User $user,
	Input $input,
	Database $db,
	Response $response,
):void {
	$upload = $uploadRepository->getById($input->getString("id"), $user);
	$db->executeSql("start transaction");
	$uploadRepository->delete($upload, $user);
	$db->executeSql("commit");
	$productRepository->calculateUncachedEarnings($user);
	$response->reload();
}
