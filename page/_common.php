<?php
use Authwave\Authenticator;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Egg\UploadMessageList;
use SHIFT\Trackshift\Upload\UploadRepository;

function go(
	ContentRepository $contentRepo,
	HTMLDocument $document,
	Binder $binder,
	UploadRepository $uploadRepository,
	Authenticator $authenticator,
	User $user,
):void {
	// TODO: Load this from the session, allowing the user to set their timezone.
	date_default_timezone_set("Europe/London");

	if($authenticator->isLoggedIn()) {
		$document->querySelector("global-header li.login")->remove();
	}
	else {
		$document->querySelector("global-header li.logout")->remove();
	}

	$document->body->dataset->set("hash", substr($user->id, -6));
	$contentRepo->bindNodeList($document->querySelectorAll("[data-content]"));
	$uploadRepository->purgeOldFiles();

	foreach($document->querySelectorAll("file-uploader") as $fileUploader) {
		$binder->bindList(new UploadMessageList(3), $fileUploader);
	}
}

function go_after(?User $user, AuditRepository $auditRepository, HTMLDocument $document):void {
	if($user && $auditRepository->isNewNotification($user)) {
		$document->querySelector("global-header .bell")->classList->add("notify");
	}
}
