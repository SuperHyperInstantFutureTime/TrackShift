<?php
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Session\Session;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Upload\UploadRepository;

function go(
	Input $input,
	Response $response,
	UploadRepository $uploadRepository,
	?User $user,
):void {
	if(!empty($uploadRepository->getUploadsForUser($user))) {
		if(!$input->contains("homepage")) {
			$response->redirect("/account/");
		}
	}
}

function do_upload(Session $session, Input $input, Response $response):void {
// TODO: I think this do function is never called, because the actual form has a different action.
	$userId = $session->getString("ulid");
	if(!$userId) {
		$response->reload();
	}

	$fileList = $input->getMultipleFile("statement");
	foreach($fileList as $file) {
		$originalFileName = $file->getClientFilename();

		$targetPath = "data/$userId/$originalFileName";
		if(!is_dir(dirname($targetPath))) {
			mkdir(dirname($targetPath), 0775, true);
		}
		$file->moveTo($targetPath);
	}

	$response->reload();
}

function do_clear(Session $session, Response $response):void {
	$userId = $session->getString("ulid");
	if(!$userId) {
		$response->reload();
	}

	foreach(glob("data/$userId/*.*") as $file) {
		unlink($file);
	}

	$response->reload();
}

function do_delete(Session $session, Input $input, Response $response):void {
	$userId = $session->getString("ulid");
	if(!$userId) {
		$response->reload();
	}

	$filename = $input->getString("filename");
	foreach(glob("data/$userId/$filename.*") as $file) {
		unlink($file);
	}

	$response->reload();
}

function do_extend(Session $session, Response $response):void {
	$userId = $session->getString("ulid");
	if(!$userId) {
		$response->reload();
	}

	$dir = "data/$userId";
	if(is_dir($dir)) {
		$dateIn3weeks = new DateTime("+3 weeks");
		touch($dir, $dateIn3weeks->getTimestamp());
	}

	$response->reload();
}
