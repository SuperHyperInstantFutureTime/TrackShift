<?php
namespace SHIFT\TrackShift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Royalty\Money;

readonly class Product extends Entity {
	public function __construct(
		public string $id,
		public string $title,
		public ?Artist $artist,
		public ?Money $totalEarning = null,
	) {}

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
	public function getTitleFormatted():?string {
		if(preg_match("/::UNSORTED_(\w+)::(\d+)/", $this->title, $matches)) {
			return "Unknown Album ($matches[1] $matches[2])";
		}

		return $this->title;
	}
}
