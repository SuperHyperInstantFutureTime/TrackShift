<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Egg\UploadMessageList;

function go(Element $element, Binder $binder):void {
	$binder->bindList(new UploadMessageList(3));
}
