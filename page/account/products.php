<?php
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;

function go(
	Binder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$binder->bindList($productRepository->getProductEarnings($user));
}
