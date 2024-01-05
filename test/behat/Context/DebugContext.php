<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert as PHPUnit;

class DebugContext extends PageContext {
	/** @Then I dump the HTML */
	public function iDumpTheHTML() {
		echo $this->getSession()->getPage()->getHtml();
		exit(1);
	}
}
