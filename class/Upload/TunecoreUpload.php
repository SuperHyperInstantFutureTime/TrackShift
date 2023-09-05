<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class TunecoreUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["TC Song ID", "Optional ISRC", "Optional UPC"];

	public function extractArtistName(array $row):string {
		return $row["Artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Release Title"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["Total Earned"]);
	}
}
