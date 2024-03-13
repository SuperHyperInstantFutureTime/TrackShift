<?php
use Gt\Dom\HTMLDocument;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Content\ContentRepository;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	HTMLDocument $document,
	User $user,
	ContentRepository $contentRepo,
	UploadRepository $uploadRepository,
):void {
	date_default_timezone_set("Europe/London");
	$document->body->dataset->set("hash", substr($user->id, -6));
	$contentRepo->bindNodeList($document->querySelectorAll("[data-content]"));
	$uploadRepository->purgeOldFiles();
	$timestamp = filemtime(".git/index");
	$document->body->dataset->set("lastUpdate", date("Y-m-d", $timestamp));
}
