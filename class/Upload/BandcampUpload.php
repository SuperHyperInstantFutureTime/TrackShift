<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class BandcampUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["item type", "item name", "artist", "bandcamp transaction id"];

	public function extractArtistName(array $row):string {
		return $row["artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["item name"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["net amount"]);
	}
}
