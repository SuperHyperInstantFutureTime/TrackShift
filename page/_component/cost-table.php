<?php
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Cost\CostRepository;

function go(
	CostRepository $costRepository,
	User $user,
	Binder $binder,
):void {
	$binder->bindList($costRepository->getAll($user));
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
