<?php
use Gt\Config\ConfigFactory;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Session\FileHandler;
use Gt\Session\Session;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

function go(
	UsageRepository $usageRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	UploadRepository $uploadRepository,
	UserRepository $userRepository,
	Database $db,
):void {
	$usageRepository->processUsageOfProducts(
		$productRepository,
		$artistRepository,
		$userRepository,
		$uploadRepository,
		$db,
	);
	$usageRepository->generateProductEarnings(
		$productRepository,
	);
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
go(
	$usageRepository,
	$artistRepository,
	$productRepository,
	$uploadRepository,
	$userRepository,
	$database,
);
