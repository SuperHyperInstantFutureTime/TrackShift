<?php
use Gt\DomTemplate\DocumentBinder;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Split\SplitRepository;

function go(
	SplitRepository $splitRepository,
	User $user,
	DocumentBinder $binder,
):void {
	$binder->bindList($splitRepository->getAll($user, true));
}
