<?php
use Gt\Database\Database;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(Response $response):void {
	$response->redirect("/account/uploads/");
}

function do_upload(
	UploadRepository $uploadRepository,
	UsageRepository $usageRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	SpotifyClient $spotify,
	User $user,
	Database $db,
	Input $input,
	Response $response,
):void {
	$startTime = microtime(true);
	set_time_limit(600);
	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));
	$time = number_format(microtime(true) - $startTime);
	Log::debug("{$time}s - Created uploads");
	$db->executeSql("START TRANSACTION");

	foreach($uploadList as $upload) {
		$usageList = $usageRepository->createUsagesFromUpload($upload);
		$time = number_format(microtime(true) - $startTime);
		Log::debug("{$time}s - Created " . count($usageList) . " usages");

		$uploadRepository->setProcessed($upload, $user);

		$processedNum = $usageRepository->process(
			$user,
			$usageList,
			$upload,
			$artistRepository,
			$productRepository,
		);

		$usageCount = count($usageList);
		$time = number_format(microtime(true) - $startTime);
		Log::debug("{$time}s - Usages created: $usageCount. Processed: $processedNum. File: $upload->filePath");

		if($upload->isrcUpcMap) {
			foreach($upload->isrcUpcMap as $isrc => $upc) {
				$productRepository->changeProductIsrcToUpc($isrc, $upc);
			}
		}

		$uploadRepository->cacheUsage($upload);
	}

	Log::debug("COMMIT");
	$db->executeSql("COMMIT");

	Log::debug("Looking up missing titles...");
	$missingTitleCount = $productRepository->lookupMissingTitles($spotify, $artistRepository, $user);
	Log::debug("Looked up $missingTitleCount titles on Spotify");
	$duplicateCount = $productRepository->deduplicate($user);
	Log::debug("De-duplicated $duplicateCount products");
	$productRepository->calculateUncachedEarnings($user);

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
