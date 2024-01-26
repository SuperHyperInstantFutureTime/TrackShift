<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Product\ProductSummary;

function go(
	Input $input,
	Binder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$page = $input->getInt("page") ?? 0;
	$limit = 10;
	$offset = $page * $limit;

	$binder->bindList($productRepository->getProductEarnings($user, $limit, $offset));
}

function do_load_summary(
	Binder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$binder->bindData($productRepository->getSummary($user));
}
