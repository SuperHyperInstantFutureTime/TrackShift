<?php
use Gt\Input\Input;
use SHIFT\Spotify\Entity\Album;
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\Entity\Track;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Product\ProductRepository;

function go(Input $input, ProductRepository $productRepository, SpotifyClient $spotify):void {
	$minimumImageSize = 80;
	$product = $productRepository->getById($input->getString("id"));
	$filePath = "data/cache/art/$product->id";
	ob_clean();

// Belt and braces...
	if(is_file($filePath)) {
		header("Content-type: image/jpeg");
		echo file_get_contents($filePath);
		exit;
	}
	if(is_file("$filePath.missing")) {
		exit;
	}

	$searchString = "album: '$product->title' artist: '{$product->artist->name}'";
	$result = $spotify->search->query($searchString, new SearchFilter(EntityType::album, EntityType::track), "GB", limit: 5);
	ob_clean();

	if(!is_dir(dirname($filePath))) {
		mkdir(dirname($filePath), recursive: true);
	}

	/** @var Album|Track $match */
	foreach(array_merge($result->albums->items, $result->tracks->items) as $match) {
		if($match->name !== $product->title) {
			continue;
		}

		if($match instanceof Track) {
			$album = $match->album;
		}
		else {
			$album = null;
		}

		if(!$album) {
			continue;
		}

		$smallestImage = null;

		foreach($album->images as $image) {
			if(is_null($smallestImage) || ($image->width < $smallestImage->width) && $image->width >= $minimumImageSize) {
				$smallestImage = $image;
			}
		}

		if(!$smallestImage) {
			http_response_code(404);
			exit;
		}

		header("Content-type: image/jpeg");
		$imageData = file_get_contents($smallestImage->url);

		file_put_contents($filePath, $imageData);
		echo $imageData;
		exit;
	}

	die("nothing");

	touch("$filePath.missing");
	exit;
}
