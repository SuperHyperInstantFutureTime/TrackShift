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
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	UploadRepository $uploadRepository,
	UsageRepository $usageRepository,
	UserRepository $userRepository,
	User $user,
	Database $db,
	Input $input,
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
	Log::debug("Created " . count($uploadList) . " uploads");

	$fileNameArray = [];
	foreach($uploadList as $upload) {
		$db->executeSql("start transaction");

		array_push($fileNameArray, $upload->basename);
// This will only be set as processed if the usages are processed successfully,
// because everything is done in a TRANSACTION.
		$uploadRepository->setProcessed($upload, $user);

// Set the default currency for the user, if they don't already have one.
		$uploadCurrency = $upload->getDefaultCurrency();
		if(is_null($userCurrency)) {
			$userCurrency = $uploadCurrency;
		}
		if(!$currentSettingsCurrency) {
			$settings->set("currency", $userCurrency->name);
			$userRepository->setUserSettings($user, $settings);
			$currentSettingsCurrency = $userCurrency;
		}

		$extractedNum = $usageRepository->extractFromUploadedFile($upload);
		$memory = round(memory_get_usage(true) / 1024 / 1024, 1);
		$seconds = number_format(microtime(true) - $startTime, 2);
		Log::debug("Extracted $extractedNum usages using $memory MB in $seconds seconds");

		$db->executeSql("commit");

		$usageRepository->processArtistsAndProducts(
			$artistRepository,
			$productRepository,
			$userRepository,
		);
	}

	$advanceTo = "/account/uploads/?received=" . implode(";", $fileNameArray);
	$response->redirect($advanceTo);
}
