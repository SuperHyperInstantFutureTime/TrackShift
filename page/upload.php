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
use SHIFT\TrackShift\Royalty\Money;
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
	$database->executeSql("PRAGMA foreign_keys = OFF");
	$database->executeSql("begin transaction");

	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));
	foreach($uploadList as $upload) {
				$usageList = $usageRepository->createUsagesFromUpload($upload);
//		$usageListTotal = $usageRepository->createUsagesFromUpload($upload);
//		$chunks = array_chunk($usageListTotal, 100);

		$artistProductTuple = [];

		$uploadRepository->setProcessed($upload);
		$processedNum = $usageRepository->process(
			$user,
			$usageList,
			$upload,
			$artistRepository,
			$productRepository,
		);

		$usageCount = count($usageList);
		Log::debug("Usages created: $usageCount. Processed: $processedNum. File: $upload->filePath");

		if($upload->isrcUpcMap) {
			$database->executeSql("end transaction");
			foreach($upload->isrcUpcMap as $isrc => $upc) {
				$productRepository->changeProductIsrcToUpc($isrc, $upc);
			}
			$database->executeSql("begin transaction");
		}

		$uploadRepository->cacheUsage($upload);
	}

	Log::debug("All processed!");

	$database->executeSql("end transaction");
	$database->executeSql("PRAGMA foreign_keys = ON");

	Log::debug("Looking up missing titles...");
	$database->executeSql("begin transaction");
	$missingTitleCount = $productRepository->lookupMissingTitles($spotify);
	Log::debug("Looked up $missingTitleCount titles on Spotify");

	$productRepository->calculateUncachedEarnings();
	$database->executeSql("end transaction");

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
