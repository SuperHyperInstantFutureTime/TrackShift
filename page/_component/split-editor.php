<?php
use Gt\Dom\Element;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Split\EmptySplitPercentage;
use SHIFT\TrackShift\Split\RemainderSplitPercentage;
use SHIFT\TrackShift\Split\SplitPercentage;
use SHIFT\TrackShift\Split\SplitRepository;

function go(
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	SplitRepository $splitRepository,
	Settings $settings,
	User $user,
	Element $element,
	Binder $binder,
	DynamicPath $dynamicPath,
	Input $input,
):void {
	$artistId = $input->getString("artist");
	$productId = $input->getString("product");
	$id = $dynamicPath->get("split");

	if($id === "_new") {
		$element->querySelector("button[name=do][value=delete]")->remove();
	}
	else {
		$split = $splitRepository->getById($id, $user);
		$productId = $split->product->id;
		$artistId = $split->product->artist->id;
		$element->querySelectorAll(".artist-product-picker select")->forEach(function(Element $select) {
			$select->setAttribute("disabled", true);
		});
	}

	$binder->bindList(
		$artistRepository->getAll($user),
		$element->querySelector("select[name=artist]"),
	);

	if($artistId) {
		$binder->bindKeyValue("artist", $artistId);

		$binder->bindList(
			$productRepository->getForArtist($artistId, $user),
			$element->querySelector("select[name=product]"),
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
		array_push($percentageList, new RemainderSplitPercentage($percentageList, $settings->get("account_name") ?: "You"));

		$binder->bindList(
			$percentageList,
			$element->querySelector(".split-percentage-list"),
		);
	}
}

function do_add_split(
	SplitRepository $splitRepository,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
//	AuditRepository $auditRepository,
	User $user,
	Input $input,
	DynamicPath $dynamicPath,
	Response $response,
):void {
	$id = $dynamicPath->get("split");

	if($id === "_new") {
		$artist = $artistRepository->getById($input->getString("artist"), $user);
		$product = $productRepository->getById($input->getString("product"));
		$split = $splitRepository->create($product, $user);
	}
	else {
		$split = $splitRepository->getById($id, $user);
	}

	$owner = $input->getString("owner");
	$percentage = $input->getFloat("percentage");
	$splitPercentage = new SplitPercentage(
		new Ulid("splitperc"),
		$owner,
		$percentage,
		$input->getString("contact"),
	);
//	$auditRepository->create($user, $splitPercentage->id, "$owner $percentage%");

	$splitRepository->addSplitPercentage($split, $splitPercentage, );
	$response->redirect("/account/splits/$split->id/?artist=$artist?->id&product=$product?->id");
}

function do_delete_split(
	SplitRepository $splitRepository,
	DynamicPath $dynamicPath,
	Input $input,
	Response $response,
//	AuditRepository $auditRepository,
	User $user,
):void {
	$splitPercentageId = $input->getString("id");
	$splitRepository->deleteSplitPercentage($splitPercentageId);

	$splitId = $dynamicPath->get("split");
	$artistId = $input->getString("artist");
	$productId = $input->getString("product");
//	$auditRepository->delete($user, $splitPercentageId);
	$response->redirect("/account/splits/$splitId/?artist=$artistId&product=$productId");
}

function do_delete(
	DynamicPath $dynamicPath,
	SplitRepository $splitRepository,
//	AuditRepository $auditRepository,
	User $user,
	Response $response,
):void {
	$splitId = $dynamicPath->get("split");
	$splitRepository->delete($splitId, $user);
//	$auditRepository->delete($user, $splitId);
	$response->redirect("../");
}
