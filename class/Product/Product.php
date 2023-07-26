<?php
namespace SHIFT\Trackshift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Repository\Entity;

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
