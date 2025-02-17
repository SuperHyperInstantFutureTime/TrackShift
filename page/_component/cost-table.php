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

	if($updatedId = $input->getString("updated")) {
		if($updatedElement = $document->querySelector("[data-cost-id='$updatedId']")) {
			$updatedElement->classList->add("new");
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
