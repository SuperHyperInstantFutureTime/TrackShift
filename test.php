<?php /** @noinspection ALL */
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\FilterQuery;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Usage\UsageRepository;

require "vendor/autoload.php";

$productTitle = "::UNSORTED_UPC::197745230202";
$spotify = new SpotifyClient("f16c2f5cbc1a41f5a9e3b90e9e38ce65", "1c495e441e3b4010a9ac0275716273a7");

$upc = substr($productTitle, strlen(UsageRepository::UNSORTED_UPC));
$result = $spotify->search->query(
	new FilterQuery(upc: $upc),
	new SearchFilter(EntityType::album),
);
$albumSearch = $result->albums->items[0] ?? null;
if($albumId = $albumSearch?->id) {
	$album = $spotify->albums->get($albumId);
	echo $album->name;
}

echo "\ndone\n";
