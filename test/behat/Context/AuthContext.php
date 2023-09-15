<?php
namespace SHIFT\Trackshift\BehatContext;

use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert as PHPUnit;

class AuthContext extends PageContext {
	/** @Then a new user ID should be generated */
	public function aNewUserIDShouldBeGenerated() {
		$body = $this->page->find("css", "body");
		PHPUnit::assertTrue($body->hasAttribute("data-hash"));
		PHPUnit::assertSame(6, strlen($body->getAttribute("data-hash")));
	}
}
