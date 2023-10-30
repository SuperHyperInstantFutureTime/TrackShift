<?php
use Gt\DomTemplate\DocumentBinder;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\ProductRepository;

function go(
	DocumentBinder $binder,
	ProductRepository $productRepository,
	User $user,
):void {
	$binder->bindList($productRepository->getProductEarnings($user));
}
