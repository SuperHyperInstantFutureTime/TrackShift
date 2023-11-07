<?php
use Gt\Dom\HTMLDocument;
use Gt\Http\Uri;

function go(HTMLDocument $document, Uri $uri):void {
	foreach($document->querySelectorAll("account-tabs a") as $tabLink) {
		$tabUri = new Uri($tabLink->href);
		if(str_starts_with($uri->getPath(), $tabUri->getPath())) {
			$tabLink->closest("li")->classList->add("selected");
		}
	}
}
