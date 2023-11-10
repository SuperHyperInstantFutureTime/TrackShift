<?php
namespace SHIFT\Trackshift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert as PHPUnit;

class NotificationContext extends PageContext {
	/** @Then I should have a notification */
	public function iShouldHaveANotification():void {
		$bell = $this->page->find("css", "global-header .bell");
		PHPUnit::assertTrue($bell->hasClass("notify"));
	}

	/** @Given the latest notification should have the message :message */
	public function theLatestNotificationShouldHaveTheMessage(string $message):void {
		$this->getSession()->visit("/account/audit/");
		$auditListFirstItem = $this->page->find("css", "audit-list>ul>li");
		$messageDiv = $auditListFirstItem->find("css", "div");
		PHPUnit::assertStringContainsString($message, $messageDiv->getText());
	}
}
