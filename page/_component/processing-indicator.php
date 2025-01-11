<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	UploadRepository $uploadRepository,
	User $user,
	Binder $binder,
	Element $element,
):void {
	$totalPercentage = $uploadRepository->getTotalPercentageProcessed($user);

	if($totalPercentage > 99) {
		$element->remove();
		return;
	}

	$binder->bindKeyValue(
		"totalPercentage",
		$totalPercentage
	);
}
