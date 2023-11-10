<?php
use Gt\DomTemplate\Binder;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\ProductRepository;

function go(
	Binder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$binder->bindList($productRepository->getProductEarnings($user));
}
