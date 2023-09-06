<?php
use DateTime;
use Gt\Dom\HTMLDocument;
use Gt\Http\Uri;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Upload\UploadManager;

function go(HTMLDocument $document, Uri $uri, ?User $user, UploadManager $uploadManager):void {
	if($user) {
		$now = new DateTime();
		if($uploadManager->getExpiry($user) < $now) {
			$uploadManager->clearUserFiles($user);
		}
	}

	foreach($document->querySelectorAll("account-tabs a") as $tabLink) {
		$tabUri = new Uri($tabLink->href);
		if($tabUri->getPath() === $uri->getPath()) {
			$tabLink->closest("li")->classList->add("selected");
		}
	}
}
