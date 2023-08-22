<?php
use Gt\Config\ConfigFactory;
use Gt\Database\Connection\DefaultSettings;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Logger\Log;
use SHIFT\Trackshift\Upload\UploadManager;

function go(UploadManager $uploadManager):void {
	if($upload = $uploadManager->getNextUploadNotYetProcessed()) {
		$startTime = microtime(true);
		$uploadManager->processUploadIntoUsages($upload);
		$t = microtime(true) - $startTime;

		Log::info("Processed upload: $upload->filePath in $t seconds");
		$uploadManager->processUsages($upload);
		$t = microtime(true) - $startTime - $t;
		Log::info("Processed usages in $t seconds");
	}
}

// TODO: Remove all of this gumpf when https://github.com/PhpGt/WebEngine/issues/639 is implemented.
cronLoopStart:
chdir(dirname(__DIR__));
require("vendor/autoload.php");
$config = ConfigFactory::createForProject(getcwd(), "vendor/phpgt/webengine/config.default.ini");
$dbSettings = new Settings(
	$config->get("database.query_directory"),
	$config->get("database.driver"),
	$config->get("database.schema"),
	$config->get("database.host"),
	$config->get("database.port"),
	$config->get("database.username"),
	$config->get("database.password"),
	$config->get("database.connection_name") ?: DefaultSettings::DEFAULT_NAME,
	$config->get("database.collation") ?: DefaultSettings::DEFAULT_COLLATION,
	$config->get("database.charset") ?: DefaultSettings::DEFAULT_CHARSET,
);
$database = new Database($dbSettings);
$uploadManager = new UploadManager(
	$database->queryCollection("Upload"),
	$database->queryCollection("Usage"),
	$database->queryCollection("Artist"),
	$database->queryCollection("Product"),
);

go($uploadManager);
sleep(1);
echo ".";
goto cronLoopStart;
