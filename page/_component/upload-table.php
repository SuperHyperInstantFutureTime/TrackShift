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
	Input $input,
	Binder $binder,
):void {
	$binder->bindList($uploadRepository->getUploadsForUser($user));

	$receivedFileList = explode(";", $input->getString("received"));
	foreach($element->querySelectorAll("tbody>tr td.basename") as $td) {
		if(in_array($td->textContent, $receivedFileList)) {
			$td->closest("tr")->classList->add("highlight");
		}
	}
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
