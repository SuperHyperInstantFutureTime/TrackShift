<?php
use Authwave\Authenticator;
use Gt\Dom\Element;
use Gt\Http\Uri;

function go(Authenticator $authenticator, Element $element, Uri $uri):void {
	if($authenticator->isLoggedIn() && $uri->getPath() !== "/") {
		$element->querySelector("li.login")?->remove();
	}
	else {
		$element->querySelector("li.logout")?->remove();
	}

	if(str_starts_with("/account/", $uri->getPath())) {
		$element->querySelector("menu [href='/]'")->closest("li")->hidden = true;
	}
	else {
		$element->querySelector("menu [href='/account/']")->closest("li")->hidden = true;
	}
}
