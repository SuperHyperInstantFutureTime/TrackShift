<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert as PHPUnit;

class ProductSteps extends PageContext {
	/** @Then I should see the total earnings for :product by :artist as :earning */
	public function iShouldSeeTheTotalEarningsForByAs(string $product, string $artist, string $earning):void {
		foreach($this->page->findAll("css", "product-table table tbody tr") as $tableRow) {
			$rowArtist = $tableRow->findAll("css", "td")[1]->getText();
			$rowProduct = $tableRow->findAll("css", "td")[2]->getText();
			$rowEarning = $tableRow->findAll("css", "td")[3]->getText();

			if($rowArtist !== $artist || $rowProduct !== $product) {
				continue;
			}

			PHPUnit::assertSame($earning, $rowEarning);
		}
	}

	/** @Given I should see the total profit for :product by :artist as :profit */
	public function iShouldSeeTheTotalProfitForByAs(string $product, string $artist, string $profit):void {
		foreach($this->page->findAll("css", "product-table table tbody tr") as $tableRow) {
			$rowArtist = $tableRow->findAll("css", "td")[1]->getText();
			$rowProduct = $tableRow->findAll("css", "td")[2]->getText();
			$rowProfit = $tableRow->findAll("css", "td")[6]->getText();

			if($rowArtist !== $artist || $rowProduct !== $product) {
				continue;
			}

			PHPUnit::assertSame($profit, $rowProfit);
		}
	}
}
