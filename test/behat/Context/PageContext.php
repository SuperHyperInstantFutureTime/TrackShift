<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Mink\Element\DocumentElement;
use Behat\MinkExtension\Context\RawMinkContext;

class PageContext extends RawMinkContext {
	protected DocumentElement $page;

	/** @BeforeScenario */
	public function setPage():void {
		$this->page = $this->getSession()->getPage();
	}
}
