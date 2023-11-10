<?php
use Gt\DomTemplate\Binder;
use SHIFT\Trackshift\Auth\Settings;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Split\SplitRepository;

function go(
	SplitRepository $splitRepository,
	User $user,
	Settings $settings,
	Binder $binder,
):void {
	$splits = $splitRepository->getAll($user, $settings->get("account_name") ?? "You");
	$binder->bindList($splits);
}
