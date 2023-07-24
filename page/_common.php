<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Upload\UploadManager;

function go(
	ContentRepository $contentRepo,
	HTMLDocument $document,
	UploadManager $uploadManager,
	User $user,
):void {
	$document->body->dataset->set("hash", substr($user->id, -6));
	// TODO: Load this from the session, allowing the user to set their timezone.
	date_default_timezone_set("Europe/London");
	$contentRepo->bindNodeList($document->querySelectorAll("[data-content]"));
	$uploadManager->purge();
}
