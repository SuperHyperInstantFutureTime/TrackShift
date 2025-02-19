<?php
use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Split\SplitRepository;

function go(
	SplitRepository $splitRepository,
	User $user,
	Settings $settings,
	Element $element,
	Input $input,
	Binder $binder,
):void {
	$splits = $splitRepository->getAll(
		$user,
		remainderName: $settings->get("account_name")
			?: "You"
	);
	$binder->bindList($splits);

	if($highlightId = $input->getString("highlight")) {
		if($highlightEl = $element->querySelector("[data-split-id='$highlightId']")) {
			$highlightEl->classList->add("highlight");
		}
	}
}
