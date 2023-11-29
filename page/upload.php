<?php
use Gt\Database\Database;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(Response $response):void {
	$response->redirect("/account/uploads/");
}

function do_upload(
	Input $input,
	Response $response,
	UserRepository $userRepository,
	User $user,
	UploadRepository $uploadRepository,
	UsageRepository $usageRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	Database $database,
):void {
	$database->executeSql("begin transaction");
	$database->executeSql("PRAGMA foreign_keys = 0");

	$userRepository->persistUser($user);
	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));

	foreach($uploadList as $upload) {
		$usageList = $usageRepository->createUsagesFromUpload($upload);
		$uploadRepository->setProcessed($upload);
		$processedNum = $usageRepository->process(
			$user,
			$usageList,
			$upload,
			$artistRepository,
			$productRepository,
		);
		$usageCount = count($usageList);
		Log::debug("Created $usageCount usages & processed $processedNum from $upload->filePath");
	}

	$database->executeSql("end transaction");
	$database->executeSql("PRAGMA foreign_keys = 1");

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
