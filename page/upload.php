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

		$artistProductTuple = [];

		foreach($chunks as $chunkIndex => $usageList) {
			$uploadRepository->setProcessed($upload);
			$chunkedArtistProductTuple = $usageRepository->process(
				$user,
				$usageList,
				$upload,
				$artistRepository,
				$productRepository,
			);
			if($artistProductTuple) {
				$artistProductTuple[0] = array_merge($artistProductTuple[0], $chunkedArtistProductTuple[0]);
				$artistProductTuple[1] = array_merge($artistProductTuple[1], $chunkedArtistProductTuple[1]);
			}
			else {
				$artistProductTuple = $chunkedArtistProductTuple;
			}

			$usageCount = count($usageList);
			$processedNum = count($chunkedArtistProductTuple);
			Log::debug("Usages created: $usageCount. Processed: $processedNum. File: $upload->filePath (iteration $chunkIndex)");
		}

		if($upload->isrcUpcMap) {
			$database->executeSql("end transaction");
			foreach($upload->isrcUpcMap as $isrc => $upc) {
				$productRepository->changeProductIsrcToUpc($isrc, $upc);
			}
			$database->executeSql("begin transaction");
		}
	}

	Log::debug("All chunks are processed!");

	$database->executeSql("end transaction");
	$database->executeSql("PRAGMA foreign_keys = 1");

	Log::debug("Looking up missing titles...");
	$missingTitleCount = $productRepository->lookupMissingTitles($spotify);
	Log::debug("Looked up $missingTitleCount titles on Spotify");

	$productRepository->calculateUncachedEarnings();

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
