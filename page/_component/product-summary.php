<?php
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;

function do_load_summary(
	Binder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$binder->bindData($productRepository->getSummary($user));
}
