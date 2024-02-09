<?php
use Gt\Dom\Element;
use Gt\Http\Uri;

function go(Element $element, Uri $uri):void {
	$uriPath = $uri->getPath();

	foreach($element->querySelectorAll("li") as $li) {
		$linkHref = $li->querySelector("a")->href;
		if(str_starts_with($uriPath, $linkHref)) {
			$li->classList->add("selected");
		}
	}
}
