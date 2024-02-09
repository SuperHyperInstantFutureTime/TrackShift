<?php
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
	Response $response,
):void {
	$upload = $uploadRepository->getById($input->getString("id"), $user);
	$uploadRepository->delete($upload, $user);
	$productRepository->calculateUncachedEarnings($user);
	$response->reload();
}
