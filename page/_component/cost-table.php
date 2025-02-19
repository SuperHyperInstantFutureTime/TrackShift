<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Cost\CostRepository;

function go(
	CostRepository $costRepository,
	User $user,
	Input $input,
	HTMLDocument $document,
	Binder $binder,
):void {
	$binder->bindList($costRepository->getAll($user));

	if($highlightId = $input->getString("highlight")) {
		if($updatedElement = $document->querySelector("[data-cost-id='$highlightId']")) {
			$updatedElement->classList->add("highlight");
		}
	}
}

function do_delete(
	CostRepository $costRepository,
	User $user,
	Input $input,
	Response $response,
):void {
	if($cost = $costRepository->getById($input->getString("id"))) {
		$costRepository->delete($cost, $user);
	}

	$response->reload();
}
