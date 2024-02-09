<?php
namespace SHIFT\TrackShift\Repository;

use Stringable;

class StringCleaner implements Stringable {
	public function __construct(private readonly string $string) {}

	public function __toString():string {
		$clean = $this->string;
		$clean = str_replace("​", "", $clean); //zero-width space
		$clean = str_replace(" ", "", $clean); //non-breaking space
		return trim($clean);
	}
}
