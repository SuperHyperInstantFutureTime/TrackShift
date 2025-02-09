<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\Binder;
use Gt\Http\Response;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Cost\Cost;
use SHIFT\TrackShift\Cost\CostRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	ArtistRepository $artistRepository,
	CostRepository $costRepository,
	ProductRepository $productRepository,
	User $user,
	Binder $binder,
	Input $input,
	HTMLDocument $document,
	DynamicPath $dynamicPath,
):void {
	$binder->bindList(
		$artistRepository->getAll($user),
		$document->querySelector("select[name=artist]"),
	);

	$artistId = $input->getString("artist");
	$id = $dynamicPath->get("cost");
	$cost = null;

	if($id === "_new") {
		$saveButton = $document->querySelector("cost-editor button[name='do'][value='save']");
		$saveButton->textContent = "Create";
		$deleteButton = $document->querySelector("cost-editor button[name='do'][value='delete']");
		$deleteButton->remove();
	}
	else {
		$cost = $costRepository->getById($id);
		$binder->bindData($cost);
		$artistId = $cost->product->artist->id;
	}

	if($artistId) {
		$binder->bindKeyValue("artist", $artistId);

		$binder->bindList(
			$productRepository->getForArtist($artistId, $user),
			$document->querySelector("select[name=product]"),
		);

		if($cost) {
			$binder->bindKeyValue("product", $cost->product->id);
		}
	}

	$binder->bindKeyValue("date", date("Y-m-d"));
}

function do_set_artist(Input $input, Response $response, Uri $uri):void {
	$artistId = $input->getString("artist");
	$response->redirect($uri->withQueryValue("artist", $artistId));
}

function do_save(
	Input $input,
	CostRepository $costRepository,
	ProductRepository $productRepository,
	UploadRepository $uploadRepository,
	Response $response,
	DynamicPath $dynamicPath,
	User $user,
):void {
	$product = $productRepository->getById($input->getString("product"));
	$uploadRepository->invalidateProfitCacheForProduct($product);
	$description = $input->getString("description");
	$amount = new Money($input->getFloat("amount"));
	$date = $input->getDateTime("date");

	$id = $dynamicPath->get("cost");

	if($id === "_new") {
		$redirectType = "created";
		$id = new Ulid("cost");
		$cost = new Cost(
			$id,
			$product,
			$description,
			$amount,
			$date,
		);
		$costRepository->create($cost, $user);
	}
	else {
		$redirectType = "updated";
		$cost = new Cost(
			$id,
			$product,
			$description,
			$amount,
			$date,
		);
		$costRepository->update($cost, $user);
	}

	$uploadRepository->cacheProfit($user);
	$response->redirect("/account/costs/?$redirectType=$id");
}

function do_delete(
	CostRepository $costRepository,
	ProductRepository $productRepository,
	UploadRepository $uploadRepository,
	User $user,
	Input $input,
	DynamicPath $dynamicPath,
	Response $response,
):void {
	$product = $productRepository->getById($input->getString("product"));
	$uploadRepository->invalidateProfitCacheForProduct($product);

	$id = $dynamicPath->get();
	if($cost = $costRepository->getById($id)) {
		$costRepository->delete($cost, $user);
	}

	$uploadRepository->cacheProfit($user);

	$response->redirect("../");
}
