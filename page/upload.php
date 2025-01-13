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
	UserRepository $userRepository,
	User $user,
	Input $input,
	Response $response,
):void {
	$settings = $userRepository->getUserSettings($user);

	$uploadList = $uploadRepository->create($user, ...$input->getMultipleFile("upload"));
	Log::debug("Created " . count($uploadList) . " uploads for user $user->id.");

	$fileNameArray = [];
	foreach($uploadList as $upload) {
		array_push($fileNameArray, $upload->basename);

// Set the default currency for the user, if they don't already have one.
//		$uploadCurrency = $upload->getDefaultCurrency();
//		if(is_null($userCurrency)) {
//			$userCurrency = $uploadCurrency;
//		}
//		if(!$currentSettingsCurrency) {
//			$settings->set("currency", $userCurrency->name);
//			$userRepository->setUserSettings($user, $settings);
//			$currentSettingsCurrency = $userCurrency;
//		}
	}

	$advanceTo = "/account/uploads/?received=" . implode(";", $fileNameArray);
	$response->redirect($advanceTo);
}
