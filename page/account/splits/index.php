<?php
use Gt\DomTemplate\Binder;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Split\SplitRepository;

function go(
	SplitRepository $splitRepository,
	User $user,
	Binder $binder,
):void {
	$splits = $splitRepository->getAll($user, true);
	$binder->bindList($splits);
}
