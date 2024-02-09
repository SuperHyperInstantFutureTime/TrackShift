<?php
use Authwave\Authenticator;
use Gt\Dom\Element;

function go(Authenticator $authenticator, Element $element):void {
	if($authenticator->isLoggedIn()) {
		$element->querySelector("li.login")->remove();
	}
	else {
		$element->querySelector("li.logout")->remove();
	}
}
