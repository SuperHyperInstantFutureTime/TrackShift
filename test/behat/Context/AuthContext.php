<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert as PHPUnit;

class AuthContext extends PageContext {
	private string $lastSeenHashData;

	/** @Then a new user ID should be generated */
	public function aNewUserIDShouldBeGenerated():void {
		$body = $this->page->find("css", "body");
		PHPUnit::assertTrue($body->hasAttribute("data-hash"));
		$hashData = $body->getAttribute("data-hash");
		PHPUnit::assertSame(6, strlen($hashData));

		$this->lastSeenHashData = $hashData;
	}

	/** @Then I should see the same user ID */
	public function iShouldSeeTheSameUserID():void {
		$body = $this->page->find("css", "body");
		$hashData = $body->getAttribute("data-hash");
		PHPUnit::assertSame($hashData, $this->lastSeenHashData);
	}
}
