<?php
use Gt\Config\Config;
use Gt\Database\Database;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Logger\Log;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Royalty\Currency;
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
	UserRepository $userRepository,
	User $user,
	Database $db,
	Input $input,
	Config $config,
	Response $response,
):void {
	$settings = $userRepository->getUserSettings($user);
	$currentSettingsCurrency = null;
	if($currencyString = $settings->get("currency") ?? null) {
		$currentSettingsCurrency = Currency::fromCode($currencyString);
	}

	$userCurrency = $currentSettingsCurrency ?? null;

	$startTime = microtime(true);
	set_time_limit(600);
	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));
	$time = number_format(microtime(true) - $startTime);
	Log::debug("{$time}s - Created uploads");
	$db->executeSql("start transaction");

	foreach($uploadList as $upload) {
//		$usageCsvFilePath = $usageRepository->createUsagesFromUpload($upload);
//		Log::debug("{$time}s - Created " . count($usageIdList) . " usages");

		$uploadRepository->setProcessed($upload, $user);

		$uploadCurrency = $upload->getDefaultCurrency();
		if(is_null($userCurrency)) {
			$userCurrency = $uploadCurrency;
		}
		if(!$currentSettingsCurrency) {
			$settings->set("currency", $userCurrency->name);
			$userRepository->setUserSettings($user, $settings);
			$currentSettingsCurrency = $userCurrency;
		}

		$processedNum = $usageRepository->process(
			$user,
			$upload,
			$artistRepository,
			$productRepository,
			$userCurrency,
		);

		$db->executeSql("COMMIT");
		$memoryPeak = memory_get_peak_usage(true);
		$memoryNow = memory_get_usage(true);
		echo(number_format($memoryNow / (1024 * 1024)) . "MB now, " . number_format($memoryPeak / (1024 * 1024)) . " MB peak\n");
		echo(number_format(microtime(true) - $startTime, 2) . " seconds");

		if($upload->isrcUpcMap) {
			foreach($upload->isrcUpcMap as $isrc => $upc) {
				$productRepository->changeProductIsrcToUpc($isrc, $upc);
			}
		}

		$uploadRepository->cacheUsage($upload);
	}

	Log::debug("committing transaction");
	$db->executeSql("commit");

	Log::debug("Looking up missing titles...");
	$missingTitleCount = $productRepository->lookupMissingTitles($spotify, $artistRepository, $user);
	Log::debug("Looked up $missingTitleCount titles on Spotify");
	$duplicateCount = $productRepository->deduplicate($user);
	Log::debug("De-duplicated $duplicateCount usingTrackshift");
	$productRepository->calculateUncachedEarnings($user);

	if($advanceTo = $input->getString("advance")) {
		$response->redirect($advanceTo);
	}
	else {
		$response->reload();
	}
}
