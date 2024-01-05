<?php
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Split\SplitRepository;

function go(
	SplitRepository $splitRepository,
	User $user,
	Settings $settings,
	Binder $binder,
):void {
	$splits = $splitRepository->getAll($user, $settings->get("account_name") ?: "You");
	$binder->bindList($splits);
}
