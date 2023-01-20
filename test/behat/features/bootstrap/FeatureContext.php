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
		$body = $this->getSession()->getPage()->find("css", "body");
		PHPUnit::assertTrue($body->hasAttribute("data-hash"));
		PHPUnit::assertSame(3, strlen($body->getAttribute("data-hash")));
	}

	/** @Then I should see :numRows rows in the table */
	public function iShouldSeeRowsInTheTable(int $numRows) {
		$table = $this->getSession()->getPage()->find("css", "table");
		if(!$table) {
			return;
		}
		$rowList = $table->findAll("css", "tbody>tr");
		PHPUnit::assertCount($numRows, $rowList);
	}

	/** @Given I should see the total earnings for :artistName as :earnings */
	public function iShouldSeeTheTotalEarningsForAs(
		string $artistName,
		string $earnings,
	) {
		foreach($this->getSession()->getPage()->findAll("css", "artist-statement-list summary h2") as $h2) {
			if($h2->getText() === $artistName) {
				PHPUnit::assertSame($earnings, $h2->getParent()->find("css", "h3")->getText());
			}
		}
	}

	/**
	 * @Given I should see :num artists
	 */
	public function iShouldSeeArtists(int $artistNum) {
		$detailsList = $this->getSession()->getPage()->findAll("css", "artist-statement-list details");
		PHPUnit::assertCount($artistNum, $detailsList);
	}

	/** @Given I should see the following table data: */
	public function iShouldSeeTheFollowingTableData(TableNode $data, $table = null) {
		if(!$table) {
			$table = $this->getSession()->getPage()->find("css", "table");
		}
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

	/** @Given I should see the following table data for :artistName: */
	public function iShouldSeeTheFollowingTableDataFor(string $artistName, TableNode $data) {
		foreach($this->getSession()->getPage()->findAll("css", "artist-statement-list summary h2") as $h2) {
			if($h2->getText() === $artistName) {
				$table = $h2->getParent()->getParent()->find("css", "table");
				$this->iShouldSeeTheFollowingTableData($data, $table);
			}
		}
	}

	/** @Then I dump the HTML */
	public function iDumpTheHTML() {
		echo $this->getSession()->getPage()->getHtml();
		exit(1);
	}
}
