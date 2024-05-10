<?php

use Authwave\Authenticator;
use Gt\Dom\HTMLDocument;

function go(Authenticator $authenticator, HTMLDocument $document):void {
	if(!$authenticator->isLoggedIn()) {
		$document->querySelector("demo-user-banner")->hidden = false;
	}
}