<?php
use Gt\Dom\HTMLDocument;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Content\ContentRepository;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	ContentRepository $contentRepo,
	UploadRepository $uploadRepository,
	HTMLDocument $document,
	User $user,
):void {
	date_default_timezone_set("Europe/London");
	$document->body->dataset->set("hash", substr($user->id, -6));
	$contentRepo->bindNodeList($document->querySelectorAll("[data-content]"));
	$uploadRepository->purgeOldFiles();
	$timestamp = filemtime(".git/index");
	$document->body->dataset->set("lastUpdate", date("Y-m-d", $timestamp));
}
