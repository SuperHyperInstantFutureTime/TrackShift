<?php
use Authwave\Authenticator;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Audit\AuditRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Content\ContentRepository;
use SHIFT\TrackShift\Egg\UploadMessageList;
use SHIFT\TrackShift\Upload\UploadRepository;

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

	$timestamp = filemtime(".git/index");
	$document->body->dataset->set("lastUpdate", date("Y-m-d", $timestamp));
}

function go_after(?User $user, AuditRepository $auditRepository, HTMLDocument $document):void {
	if($user && $auditRepository->isNewNotification($user)) {
		$document->querySelector("global-header .bell")->classList->add("notify");
	}
}
