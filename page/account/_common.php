<?php
use Gt\Dom\HTMLDocument;
use Gt\Http\Uri;

function go(HTMLDocument $document, Uri $uri):void {
	foreach($document->querySelectorAll("account-tabs a") as $tabLink) {
		$tabUri = new Uri($tabLink->href);
		if($tabUri->getPath() === $uri->getPath()) {
			$tabLink->closest("li")->classList->add("selected");
		}
	}
}
