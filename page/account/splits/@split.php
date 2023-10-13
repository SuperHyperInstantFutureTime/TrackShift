<?php

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Split\EmptySplitPercentage;
use SHIFT\Trackshift\Split\RemainderSplitPercentage;
use SHIFT\Trackshift\Split\SplitPercentage;
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

	if($id === "_new") {
//		$document->querySelector("button[name=do][value=save]")->textContent = "Create";
		$document->querySelector("button[name=do][value=delete]")->remove();
	}
	else {

	}

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

		if($id === "_new") {
			$percentageList = [];
		}
		else {
			$percentageList = $splitRepository->getSplitPercentageList($user, $id);
		}
		array_push($percentageList, new EmptySplitPercentage($productId));
		array_push($percentageList, new RemainderSplitPercentage($percentageList));
		$binder->bindList(
			$percentageList,
			$document->querySelector(".split-percentage-list"),
		);
	}
}

function do_add_split(
	Input $input,
	DynamicPath $dynamicPath,
	SplitRepository $splitRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	User $user,
	Response $response,
):void {
	$id = $dynamicPath->get("split");
	$artist = $artistRepository->getById($input->getString("artist"));
	$product = $productRepository->getById($input->getString("product"));

	if($id === "_new") {
		$split = $splitRepository->create($product, $user);
	}
	else {
		$split = $splitRepository->getById($id, $user);
	}

	$splitPercentage = new SplitPercentage(
		new Ulid("splitperc"),
		$split,
		$input->getString("owner"),
		$input->getFloat("percentage"),
		$input->getString("contact"),
	);

	$splitRepository->addSplitPercentage($splitPercentage);
	$response->redirect("/account/splits/$split->id/?artist=$artist->id&product=$product->id");
}

function do_delete_split(
	SplitRepository $splitRepository,
	DynamicPath $dynamicPath,
	Input $input,
	Response $response,
):void {
	$splitPercentageId = $input->getString("id");
	$splitRepository->deleteSplitPercentage($splitPercentageId);

	$splitId = $dynamicPath->get("split");
	$artistId = $input->getString("artist");
	$productId = $input->getString("product");
	$response->redirect("/account/splits/$splitId/?artist=$artistId&product=$productId");
}

function do_delete(
	DynamicPath $dynamicPath,
	SplitRepository $splitRepository,
	User $user,
	Response $response,
):void {
	$splitId = $dynamicPath->get("split");
	$splitRepository->delete($splitId, $user);
	$response->redirect("/account/splits");
}
