<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Routing\Path\DynamicPath;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Cost\Cost;
use SHIFT\Trackshift\Cost\CostRepository;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Royalty\Money;

function go(
	HTMLDocument $document,
	DocumentBinder $binder,
	DynamicPath $dynamicPath,
	Input $input,
	ArtistRepository $artistRepository,
	ProductRepository $productRepository,
	CostRepository $costRepository,
	User $user,
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
			$productRepository->getForArtist($artistId),
			$document->querySelector("select[name=product]"),
		);

		if($cost) {
			$binder->bindKeyValue("product", $cost->product->id);
		}
	}
}

function do_set_artist(Input $input, Response $response, Uri $uri):void {
	$artistId = $input->getString("artist");
	$response->redirect($uri->withQueryValue("artist", $artistId));
}

function do_save(
	Input $input,
	CostRepository $costRepository,
	ProductRepository $productRepository,
	Response $response,
	DynamicPath $dynamicPath,
):void {
	$product = $productRepository->getById($input->getString("product"));
	$description = $input->getString("description");
	$amount = new Money($input->getFloat("amount"));

	$id = $dynamicPath->get("cost");

	if($id === "_new") {
		$id = new Ulid("cost");
		$cost = new Cost(
			$id,
			$product,
			$description,
			$amount,
		);
		$costRepository->create($cost);
		$response->redirect("/account/costs/?created=$id");
	}
	else {
		$cost = new Cost(
			$id,
			$product,
			$description,
			$amount,
		);
		$costRepository->update($cost);
		$response->redirect("/account/costs/?updated=$id");
	}
}
