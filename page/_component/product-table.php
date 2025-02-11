<?php
use DateTime;
use Gt\DomTemplate\Binder;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Royalty\Currency;

function go(
	ProductRepository $productRepository,
	Settings $settings,
	User $user,
	Input $input,
	Binder $binder,
):void {
	$page = $input->getInt("page") ?? 0;
	$limit = 10;
	$offset = $page * $limit;

	$binder->bindList($productRepository->getProductTableData(
		$user,
		$limit,
		$offset,
		$input->getDateTime("p-from") ?? new DateTime("1970-01-01"),
		$input->getDateTime("p-to") ?? new DateTime("2999-12-31"),
	));
}
