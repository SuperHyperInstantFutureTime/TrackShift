<?php
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;

function go(
	ProductRepository $productRepository,
	User $user,
	Input $input,
	Binder $binder,
):void {
	$page = $input->getInt("page") ?? 0;
	$limit = 10;
	$offset = $page * $limit;

	$binder->bindList($productRepository->getProductEarnings($user, $limit, $offset));
}
