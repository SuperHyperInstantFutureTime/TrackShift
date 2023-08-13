<?php
use Gt\Input\Input;
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Product\ProductRepository;

function go(Input $input, ProductRepository $productRepository, SpotifyClient $spotify):void {
	$minimumImageSize = 80;
	$product = $productRepository->getById($input->getString("id"));
	$filePath = "data/cache/art/$product->id";

// Belt and braces...
	if(is_file($filePath)) {
		ob_clean();
		header("Content-type: image/jpeg");
		echo file_get_contents($filePath);
		exit;
	}
	if(is_file("$filePath.missing")) {
		exit;
	}

	$searchString = "album: '$product->title' artist: '{$product->artist->name}'";
	$result = $spotify->search->query($searchString, new SearchFilter(EntityType::album), "GB", limit: 1);
	if($album = $result->albums->items[0] ?? null) {
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

		ob_clean();
		header("Content-type: image/jpeg");
		$imageData = file_get_contents($smallestImage->url);
		if(!is_dir(dirname($filePath))) {
			mkdir(dirname($filePath), recursive: true);
		}

		file_put_contents($filePath, $imageData);
		echo $imageData;
		exit;
	}
	else {
		touch("$filePath.missing");
		exit;
	}
}
