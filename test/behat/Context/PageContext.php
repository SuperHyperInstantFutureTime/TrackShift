<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\DocumentElement;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert as PHPUnit;

class PageContext extends RawMinkContext {
	protected DocumentElement $page;

	/** @BeforeScenario */
	public function setPage():void {
		$this->page = $this->getSession()->getPage();
	}
}
