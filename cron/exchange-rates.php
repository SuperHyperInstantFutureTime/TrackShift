<?php
use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use SHIFT\TrackShift\Royalty\CurrencyExchange;

function go(Config $config):void {
	$exchange = new CurrencyExchange();
	$exchange->generateCache($config->getString("openexchangerates.app_id"));
}

// TODO: Handle cron like page/api

chdir(dirname(__DIR__));
require "vendor/autoload.php";
$config = ConfigFactory::createForProject(
	getcwd(),
	"vendor/phpgt/webengine/config.default.ini"
);

go($config);
