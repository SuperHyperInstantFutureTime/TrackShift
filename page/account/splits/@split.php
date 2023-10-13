<?php

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Split\EmptySplitPercentage;
use SHIFT\Trackshift\Split\SplitRepository;

function go(
	HTMLDocument $document,
	DocumentBinder $binder,
	DynamicPath $dynamicPath,
	Input $input,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	SplitRepository $splitRepository,
	User $user,
):void {
	$artistId = $input->getString("artist");
	$productId = $input->getString("product");
	$id = $dynamicPath->get("split");

	$percentageList = $splitRepository->getPercentageList($user, $productId);
	array_push($percentageList, new EmptySplitPercentage());
	array_push($percentageList, new RemainderSplitPercentage());

	if($id === "_new") {
		$document->querySelector("button[name=do][value=save]")->textContent = "Create";
		$document->querySelector("button[name=do][value=delete]")->remove();
	}
	else {

	}

	$binder->bindList(
		$percentageList,
		$document->querySelector(".split-percentage-list"),
	);

	$binder->bindList(
		$artistRepository->getAll($user),
		$document->querySelector("select[name=artist]"),
	);

	if($artistId) {
		$binder->bindKeyValue("artist", $artistId);

		$binder->bindList(
			$productRepository->getForArtist($artistId),
			$document->querySelector("select[name=product]"),
		);
	}
	if($productId) {
		$binder->bindKeyValue("product", $productId);
	}
}
