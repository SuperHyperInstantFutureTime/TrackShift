<?php
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Gt\Daemon\Process;
use PHPUnit\Framework\Assert as PHPUnit;

class FeatureContext extends MinkContext {
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
		$socket = @fsockopen(
			"localhost",
			$serverPort,
			$errorCode,
			$errorMessage,
			1
		);
		if(!$socket) {
			if(!is_dir("www")) {
				mkdir("www");
			}
			$path = realpath(__DIR__ . "/../../../../");
			self::$server = new Process("php", "-S", "$serverAddress:$serverPort", "-t", "www", "./vendor/phpgt/webengine/go.php");
			self::$server->setExecCwd($path);
			self::$server->exec();
		}
	}

	/** @Then a new user ID should be generated */
	public function aNewUserIDShouldBeGenerated() {
		$url = $this->getSession()->getCurrentUrl();
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $queryParts);
		PHPUnit::assertArrayHasKey("user", $queryParts);
		PHPUnit::assertGreaterThan(16, strlen($queryParts["user"]));
	}

	/** @Then I should see :numRows rows in the table */
	public function iShouldSeeRowsInTheTable(int $numRows) {
		$table = $this->getSession()->getPage()->find("css", "table");
		PHPUnit::assertNotNull($table, "Table not found on page");
		$rowList = $table->findAll("css", "tbody>tr");
		PHPUnit::assertCount($numRows, $rowList);
	}

	/** @Given I should see the following table data: */
	public function iShouldSeeTheFollowingTableData(TableNode $data) {
		$table = $this->getSession()->getPage()->find("css", "table");
		$trList = $table->findAll("css", "tr");
		$rowIndex = 0;

		foreach($data->getTable() as $dataRow) {
			$tr = $trList[$rowIndex];
			$tdList = $tr->findAll("css", "th,td");

			foreach($dataRow as $columnIndex => $text) {
				$td = $tdList[$columnIndex];
				PHPUnit::assertSame($text, $td->getText());
			}

			$rowIndex++;
		}
	}

	/** @Then I dump the HTML */
	public function iDumpTheHTML() {
		echo $this->getSession()->getPage()->getHtml();
		exit(1);
	}
}
