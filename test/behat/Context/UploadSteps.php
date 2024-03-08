<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as PHPUnit;

class UploadSteps extends PageContext {
	/** @Given I should see the file uploader */
	public function iShouldSeeTheFileUploader() {
		$fileUploader = $this->page->find("css", "global-header file-uploader");
	}

	/** @Given I should see the following table data: */
	public function iShouldSeeTheFollowingTableData(TableNode $data, $table = null): void {
		if(!$table) {
			$table = $this->page->find("css", "table:not(.supplementary)");
		}
		$trList = $table->findAll("css", "tr");
		$firstTr = array_shift($trList);
		$tableColumnOrder = [];
		$headingRow = current($data->getTable());

		foreach($firstTr->findAll("css", "th,td") as $columnIndex => $tableCell) {
			$text = $tableCell->getText();
			if(!in_array($text, $headingRow)) {
				continue;
			}
			$tableColumnOrder[$text] = $columnIndex;
		}

		PHPUnit::assertCount(count($headingRow), $tableColumnOrder, "Not all required table headings are present");

		$hash = $data->getColumnsHash();
		foreach($hash as $assertionIndex => $kvp) {
			foreach($kvp as $key => $value) {
				$foundTextToCompareWithValue = [];

				$columnToCheck = $tableColumnOrder[$key] ?? null;
				if($columnToCheck !== null) {
					foreach($trList as $tr) {
						$tdList = $tr->findAll("css", "td");
						array_push(
							$foundTextToCompareWithValue,
							$tdList[$columnToCheck]->getText(),
						);
					}
				}

				PHPUnit::assertContains($value, $foundTextToCompareWithValue, implode(" ", $foundTextToCompareWithValue));
			}
		}
	}

	/** @Then I should see :numRows row(s) in the table */
	public function iShouldSeeRowsInTheTable(int $numRows) {
		$table = $this->getSession()->getPage()->find("css", "table");
		if(!$table) {
			return;
		}
		$rowList = $table->findAll("css", "tbody>tr");
		PHPUnit::assertCount($numRows, $rowList);
	}

	/** @Given I upload the file :fileName */
	public function iUploadTheFile(string $fileName):void {
		$this->visitPath("/account/uploads/");
		$this->page->attachFileToField("upload[]", "test/files/$fileName");
		$this->page->pressButton("Upload");
	}

	/** @Then I should go to :newPathName */
	public function iShouldGoTo(string $newPathName):void {
		$responseHeaders = $this->getSession()->getResponseHeaders();
		var_dump($responseHeaders);die();
	}

	/** @When I delete the upload :fileName */
	public function iDeleteTheUpload(string $fileNameToDelete):void {
		$deleteButton = null;
		$uploadTableRowList = $this->page->findAll("css", "upload-table tbody tr");
		foreach($uploadTableRowList as $rowElement) {
			$fileNameCell = $rowElement->find("css", ".basename");
			$rowFileName = $fileNameCell->getText();
			if($rowFileName !== $fileNameToDelete) {
				continue;
			}

			$deleteButton = $rowElement->find("css", "button[name=do][value=delete]");
			$deleteButton->click();
			break;
		}

		PHPUnit::assertNotNull($deleteButton, "No matching filename: $fileNameToDelete");
	}
}
