<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert as PHPUnit;

class SplitSteps extends PageContext {

	/** @Then I should see :numSplits product splits */
	public function iShouldSeeProductSplits(int $numSplits):void {
		$allProductSplits = $this->page->findAll("css", "splits-list>ul>li");
		PHPUnit::assertCount(0, $allProductSplits);
	}

	/** @Then I should see my split as :percentage */
	public function iShouldSeeMySplitAs(string $percentage) {
		$percentage = trim($percentage, "%");
		$splitPercentageForms = $this->page->findAll("css", "form.split-percentage");
		/** @var NodeElement $mySplitPercentageForm */
		$mySplitPercentageForm = end($splitPercentageForms);
		PHPUnit::assertSame("You", $mySplitPercentageForm->findField("owner")->getValue());
		PHPUnit::assertSame($percentage, $mySplitPercentageForm->findField("percentage")->getValue());
	}

	/** @When I add a split of :percentage to :owner with the contact details of :contact */
	public function iAddASplitOfToWithTheContactDetailsOf(string $percentage, string $owner, string $contact):void {
		$percentage = trim($percentage, "%");
		$splitPercentageForms = $this->page->findAll("css", "form.split-percentage");
		/** @var NodeElement $mySplitPercentageForm */
		$newSplitPercentageForm = $splitPercentageForms[count($splitPercentageForms) - 2];
		$newSplitPercentageForm->findField("owner")->setValue($owner);
		$newSplitPercentageForm->findField("percentage")->setValue($percentage);
		$newSplitPercentageForm->findField("contact")->setValue($contact);
		$newSplitPercentageForm->findButton("Add")->click();
	}

	/** @Given I should see a split for :owner of :percentage */
	public function iShouldSeeASplitFor(string $owner, string $percentage):void {
		$percentage = trim($percentage, "%");
		$splitPercentageForms = $this->page->findAll("css", "form.split-percentage");

		foreach($splitPercentageForms as $splitPercentageForm) {
			if($splitPercentageForm->findField("owner")->getValue() !== $owner) {
				continue;
			}

			PHPUnit::assertSame($percentage, $splitPercentageForm->findField("percentage")->getValue());
		}
	}
}
