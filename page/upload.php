<?php
use Gt\Database\Database;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\Spotify\SpotifyClient;
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
	SpotifyClient $spotify,
	Database $database,
):void {
	set_time_limit(600);
	$database->executeSql("begin transaction");
	$database->executeSql("PRAGMA foreign_keys = 0");

	$userRepository->persistUser($user);
	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));
	foreach($uploadList as $upload) {
		$usageListTotal = $usageRepository->createUsagesFromUpload($upload);
		$chunks = array_chunk($usageListTotal, 100);

		foreach($chunks as $chunkIndex => $usageList) {
			$uploadRepository->setProcessed($upload);
			$processedNum = $usageRepository->process(
				$user,
				$usageList,
				$upload,
				$artistRepository,
				$productRepository,
			);
			$usageCount = count($usageList);
			Log::debug("Usages created: $usageCount. Processed: $processedNum. File: $upload->filePath (iteration $chunkIndex)");
		}
	}

	Log::debug("All chunks are processed!");

	$database->executeSql("end transaction");
	$database->executeSql("PRAGMA foreign_keys = 1");

	Log::debug("Looking up missing titles...");
	$missingTitleCount = $productRepository->lookupMissingTitles($spotify);
	Log::debug("Looked up $missingTitleCount titles on Spotify");

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
