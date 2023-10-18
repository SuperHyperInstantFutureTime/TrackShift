<?php
namespace SHIFT\Trackshift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as PHPUnit;

class SplitContext extends PageContext {

	/** @Then I should see :numSplits product splits */
	public function iShouldSeeProductSplits(int $numSplits):void {
		$allProductSplits = $this->page->findAll("css", "splits-list>ul>li");
		PHPUnit::assertCount(0, $allProductSplits);
	}
}
