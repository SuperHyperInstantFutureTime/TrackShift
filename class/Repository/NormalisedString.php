<?php
namespace SHIFT\TrackShift\Repository;

use Stringable;

class NormalisedString implements Stringable {
	public function __construct(private readonly string $text) {}

	public function __toString():string {
		$text = $this->text;
		$text = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $text);
		$text = preg_replace("/[^\w ]/", "", $text);
		$text = str_replace(" ", "_", $text);
		return strtolower($text);
	}
}
