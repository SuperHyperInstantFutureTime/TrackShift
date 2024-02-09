<?php
namespace SHIFT\TrackShift\BehatContext;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
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

	/** @Then the authentication area should show :num button */
	public function theAuthenticationAreaShouldShowButton(int $expectedNumButtons):void {
		$menuElement = $this->page->find("css", "global-header nav menu");
		PHPUnit::assertNotNull($menuElement);
		$loginElement = $menuElement->find("css", "li.login");
		$logoutElement = $menuElement->find("css", "li.logout");

		$actualNumButtons = (is_null($loginElement) ? 0 : 1) +(is_null($logoutElement) ? 0 : 1);
		PHPUnit::assertSame($actualNumButtons, $actualNumButtons);
	}

	/** @Then the authentication button should read :text */
	public function theAuthenticationButtonShouldRead(string $expectedButtonText):void {
		$menuElement = $this->page->find("css", "global-header nav menu");
		$loginElement = $menuElement->find("css", "li.login span");
		$logoutElement = $menuElement->find("css", "li.logout span");

		$authenticationButton = $loginElement ?? $logoutElement;
		PHPUnit::assertSame($expectedButtonText, $authenticationButton->getText(), $authenticationButton->getText());
	}

	/** @Then I should see the following tabs: */
	public function iShouldSeeTheFollowingTabs(TableNode $table):void {
		$tabsElement = $this->page->find("css", "account-tabs");
		PHPUnit::assertNotNull($tabsElement);
		$linkList = $tabsElement->findAll("css", "a");

		foreach($table->getRows() as $i => $whatever) {
			$expectedTabTitle = $whatever[0];
			$link = $linkList[$i];
			PHPUnit::assertSame($expectedTabTitle,$link->getText());
		}
	}

	/** @Then the :tabName tab should be selected	*/
	public function theTabShouldBeSelected(string $expectedTabName):void {
		$tabLiList = $this->page->findAll("css", "account-tabs li");
		foreach($tabLiList as $liElement) {
			$currentTabName = $liElement->getText();
			if($currentTabName === $expectedTabName) {
				PHPUnit::assertTrue($liElement->hasClass("selected"), "Tab '$currentTabName' should be selected, but isn't");
			}
			else {
				PHPUnit::assertFalse($liElement->hasClass("selected"), "Tab '$currentTabName' shouldn't be selected, but is");
			}
		}
	}
}
