<?php
use Gt\Input\Input;
use SHIFT\Spotify\Entity\Album;
use SHIFT\Spotify\Entity\AlbumType;
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\FilterQuery;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\Entity\Track;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Product\ProductRepository;

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

	$filterQuery = new FilterQuery(
		album: $product->title,
		artist: $product->artist->name,
	);
	$result = $spotify->search->query($filterQuery, new SearchFilter(EntityType::album, EntityType::track), limit: 5);

	if(!is_dir(dirname($filePath))) {
		mkdir(dirname($filePath), recursive: true);
	}

	/** @var Album|Track $match */
	foreach(array_merge($result->albums->items, $result->tracks->items) as $match) {
		if($match instanceof Track) {
			$album = $match->album;
		}
		else {
			$album = $match;
		}

		if(!$album) {
			echo "No album on $match->name", PHP_EOL;
			continue;
		}

		$albumMatch = strtolower($album->name);
		$albumMatch = str_replace(["[", "]", "{", "}", "(", ")"], "", $albumMatch);
		$productMatch = strtolower($product->title);
		$productMatch = str_replace(["[", "]", "{", "}", "(", ")"], "", $productMatch);
		if($albumMatch !== $productMatch) {
			echo "Album:\t\"$album->name\"", PHP_EOL;
			echo "Prod:\t\"$product->title\"...", PHP_EOL;
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

		ob_clean();
		header("Content-type: image/jpeg");
		$imageData = file_get_contents($smallestImage->url);

		file_put_contents($filePath, $imageData);
		echo $imageData;
		exit;
	}

	touch("$filePath.missing");
	header("Content-type: image/svg");
	readfile("asset/img/product/ts_album_placeholder.svg");
	exit;
}
