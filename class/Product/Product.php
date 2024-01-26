<?php
namespace SHIFT\TrackShift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Royalty\Money;

readonly class Product extends Entity {
	public function __construct(
		public string $id,
		private string $title,
		public ?Artist $artist,
		public ?Money $totalEarning = null,
	) {}

// TODO: Refactor usage of $product->title into $product->getTitle()
	public function __get(string $name) {
		if($name === "title") {
			return $this->title;
		}
	}

	#[BindGetter]
	public function getArtUrl():?string {
		$filePath = "data/cache/art/$this->id";
		if(is_file($filePath)) {
			return "/$filePath";
		}

		if(is_file("$filePath.missing")) {
			return null;
		}

		return "/lazy-load/?id=$this->id";
	}

	#[BindGetter]
	public function getTitle():?string {
		if(preg_match("/::UNSORTED_UPC::(\d+)/", $this->title, $matches)) {
			return "Unknown Album (UPC $matches[1])";
		}

		return $this->title;
	}
}
