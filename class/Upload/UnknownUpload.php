<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;

class UnknownUpload extends Upload {
	protected function processUsages():void {}

	// phpcs:ignore
	public function extractArtistName(array $row):string {
		return "Unknown";
	}

	// phpcs:ignore
	public function extractProductTitle(array $row):string {
		return "Unknown";
	}

	// phpcs:ignore
	public function extractEarning(array $row):Money {
		return new Money(0);
	}

	public function extractEarningDate(array $row):DateTime { // phpcs:ignore
		return new DateTime("1970-01-01");
	}
}
