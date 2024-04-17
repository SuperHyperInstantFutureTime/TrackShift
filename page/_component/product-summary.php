<?php
use DateTime;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;

function do_load_summary(
	ProductRepository $productRepository,
	Binder $binder,
	User $user,
	Input $input,
):void {
	$binder->bindData($productRepository->getSummary(
		$user,
		$input->getDateTime("p-from") ?? new DateTime("1970-01-01"),
		$input->getDateTime("p-to") ?? new DateTime("2999-12-31"),
	));
}
