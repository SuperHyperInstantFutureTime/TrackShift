<?php
namespace SHIFT\TrackShift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Repository\Entity;

readonly class Product extends Entity {
	public function __construct(
		public string $id,
		public string $title,
		public Artist $artist,
	) {}

	#[BindGetter]
	public function getArtUrl():?string {
		$filePath = "data/cache/art/$this->id";
		if(is_file($filePath)) {
			return "/$filePath";
		}

		return null;
	}
}
