<?php
use Gt\Config\ConfigFactory;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Input\Input;
use Gt\Logger\Log;
use Gt\Session\FileHandler;
use Gt\Session\Session;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\DatabaseTransaction;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(
	UsageRepository $usageRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	UploadRepository $uploadRepository,
	UserRepository $userRepository,
	Database $db,
	DatabaseTransaction $dbTransaction,
	Input $input,
):void {
	$processUploadsIntoUsages = false;
	$processProductUsages = false;
	$processProductEarnings = false;
	$type = $input->getInt("type");

// TODO: We need to figure out the default currency for users that don't have one! BEFORE any earnings are calculated.

	if(is_null($type)) {
		$processUploadsIntoUsages = $processProductUsages = $processProductEarnings = true;
	}
	if($type >= 1) {
		$processUploadsIntoUsages = true;
	}
	if($type >= 2) {
		$processProductUsages = true;
	}
	if($type >= 3) {
		$processProductEarnings = true;
	}

	if($processUploadsIntoUsages) {
		$time = microtime(true);

		$uploadRepository->purgeOldFiles();
		$i = null;
		foreach($uploadRepository->getUnprocessed() as $i => $upload) {
			$uploadDefaultCurrency = $upload->getDefaultCurrency();
			$user = $userRepository->getById($upload->userId);
			$userSettings = $userRepository->getUserSettings($user);
			$currentUserCurrency = $userSettings->get("currency");
			if(!$currentUserCurrency) {
				$userSettings->set("currency", $uploadDefaultCurrency->name);
				$userRepository->setUserSettings($user, $userSettings);
			}

			$dbTransaction->start();
			$usageRepository->extractProductsFromUpload($upload);
			$uploadRepository->setProcessed($upload);
			$dbTransaction->commit();
		}
		if(is_null($i)) {
			Log::debug("No new uploads.");
		}
		else {
			$i += 1;
			$deltaTime = number_format(microtime(true) - $time, 2);
			Log::info("Extracted products from $i uploads in $deltaTime seconds.");
		}
	}

	if($processProductUsages) {
		$time = microtime(true);
		$numUsagesProcessed = $usageRepository->processUsageOfProducts(
			$productRepository,
			$artistRepository,
			$userRepository,
			$uploadRepository,
			$db,
		);
		if($numUsagesProcessed > 0) {
			$deltaTime = number_format(microtime(true) - $time, 2);
			Log::info("Processed $numUsagesProcessed usages in $deltaTime seconds.");
		}
		else {
			Log::debug("No new usages to process.");
		}
	}

	if($processProductEarnings) {
		$time = microtime(true);
		$numProductsCalculated = $usageRepository->calculateProductEarnings(
			$productRepository,
			$dbTransaction,
		);

		if($numProductsCalculated > 0) {
			$deltaTime = number_format(microtime(true) - $time, 2);
			Log::info("Calculated $numProductsCalculated earnings in $deltaTime seconds.");
		}
		else {
			Log::debug("No new products generated.");
		}
	}

	$uploadRepository->cacheEarnings();
}

// TODO: Handle cron like page/api

chdir(dirname(__DIR__));
require "vendor/autoload.php";
$config = ConfigFactory::createForProject(
	getcwd(),
	"vendor/phpgt/webengine/config.default.ini"
);

$handler = new FileHandler();
$session = new Session($handler);

$settings = new Settings(
	$config->getString("database.query_directory"),
	$config->getString("database.driver"),
	$config->getString("database.schema"),
	$config->getString("database.host"),
	$config->getInt("database.port"),
	$config->getString("database.username"),
	$config->getString("database.password"),
);
$database = new Database($settings);

$usageRepository = new UsageRepository($database->queryCollection("Usage"));
$artistRepository = new ArtistRepository($database->queryCollection("Artist"));
$productRepository = new ProductRepository($database->queryCollection("Product"), $artistRepository);
$uploadRepository = new UploadRepository($database->queryCollection("Upload"));
$userRepository = new UserRepository($database->queryCollection("User"), $session->getStore(UserRepository::SESSION_STORE_KEY, true));


// Parse $argv into a key-value pair associative array
$_GET = [];
foreach($argv as $arg) {
	if(preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
		// Long option with value (e.g., --key=value)
		$_GET[$matches[1]] = $matches[2];
	}
	elseif(preg_match('/^--(.+)$/', $arg, $matches)) {
		// Long option without value (e.g., --key)
		$_GET[$matches[1]] = true;
	}
	elseif(preg_match('/^-([a-zA-Z])$/', $arg, $matches)) {
		// Short option (e.g., -k)
		$_GET[$matches[1]] = true;
	}
}
$input = new Input($_GET);

$transaction = new DatabaseTransaction($database);

go(
	$usageRepository,
	$artistRepository,
	$productRepository,
	$uploadRepository,
	$userRepository,
	$database,
	$transaction,
	$input,
);
