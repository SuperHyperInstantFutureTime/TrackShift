<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Gt\Daemon\Process;

class ServerContext implements Context {
	private static Process $server;

	/** @BeforeSuite */
	public static function setUp(BeforeSuiteScope $scope):void {
		$contextSettings = $scope->getSuite()->getSettings();
		self::checkServerRunning(
			$contextSettings["serverAddress"],
			$contextSettings["serverPort"],
		);
	}

	/** @AfterSuite */
	public static function tearDown():void {
		if(isset(self::$server)) {
			self::$server->terminate();
		}
	}

	private static function checkServerRunning(
		string $serverAddress,
		int $serverPort,
	):void {
		$socket = null;

		while(!$socket) {
			$socket = @fsockopen(
				"localhost",
				$serverPort,
				$errorCode,
				$errorMessage,
				1
			);

			if($socket) {
				break;
			}

			if(!is_dir("www")) {
				mkdir("www");
			}
			$path = realpath(__DIR__ . "/../../../../");
			self::$server = new Process("php", "-S", "$serverAddress:$serverPort", "-t", "www", "./vendor/phpgt/webengine/go.php");
			self::$server->setExecCwd($path);
			self::$server->exec();
			sleep(1);
			echo "Server started...", PHP_EOL;
		}
	}
}
