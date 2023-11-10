<?php
use Gt\Database\Database;
use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Egg\UploadMessageList;
use SHIFT\Trackshift\Upload\UploadManager;

function go(
	ContentRepository $contentRepo,
	HTMLDocument $document,
	DocumentBinder $binder,
	UploadManager $uploadManager,
	?User $user,
):void {
	// TODO: Load this from the session, allowing the user to set their timezone.
	date_default_timezone_set("Europe/London");

	if(empty($uploadManager->getUploadsForUser($user))) {
		$document->querySelectorAll("global-header nav a")->forEach(fn(Element $element) => $element->remove());
	}

	$document->body->dataset->set("hash", substr($user->id, -6));
	$contentRepo->bindNodeList($document->querySelectorAll("[data-content]"));
	$uploadManager->purgeOldFiles();

	foreach($document->querySelectorAll("file-uploader") as $fileUploader) {
		$binder->bindList(new UploadMessageList(3), $fileUploader);
	}
}

function go_after(?User $user, AuditRepository $auditRepository, HTMLDocument $document):void {
	if($user && $auditRepository->isNewNotification($user)) {
		$document->querySelector("global-header .bell")->classList->add("notify");
	}
}
