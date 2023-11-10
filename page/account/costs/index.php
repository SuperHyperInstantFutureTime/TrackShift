<?php
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Cost\CostRepository;

function go(Binder $binder, CostRepository $costRepository, User $user):void {
	$binder->bindList(
		$costRepository->getAll($user)
	);
}

function do_delete(Input $input, CostRepository $costRepository, Response $response, User $user):void {
	$costRepository->delete($input->getString("id"), $user);
	$response->reload();
}
