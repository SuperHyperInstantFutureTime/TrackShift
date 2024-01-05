<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as PHPUnit;

class UploadContext extends PageContext {
	/** @Given I should see the following table data: */
	public function iShouldSeeTheFollowingTableData(TableNode $data, $table = null): void {
		if(!$table) {
			$table = $this->page->find("css", "table");
		}
		$trList = $table->findAll("css", "tr");
		$firstTr = array_shift($trList);
		$tableColumnOrder = [];
		$headingRow = current($data->getTable());

		foreach($firstTr->findAll("css", "th,td") as $columnIndex => $tableCell) {
			$text = $tableCell->getText();
			$matchingColumn = array_search($text, $headingRow);
			if(false === $matchingColumn) {
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

				PHPUnit::assertContains($value, $foundTextToCompareWithValue);
			}
//
//			foreach($trList as $tr) {
//				$tdList = $tr->findAll("css", "td");
//				foreach($kvp as $key => $value) {
//					if($columnToCheck = $tableColumnOrder[$key] ?? null) {
//						$columnValue = $tdList[$columnToCheck]->getText();
//						if($value !== $columnValue) {
//							$matchingTr = false;
//						}
//					}
//				}
//				if($matchingTr) {
//					break;
//				}
//			}
//
//			PHPUnit::assertTrue($found, "Table data missing: $assertionIndex\n" . print_r($kvp, true));
		}
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
}
