<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class CargoUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["Royalty ID", "Asset ISRC", "Sale net receipts"];

	public function extractArtistName(array $row):string {
		return $row["Asset Artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Product Title"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["Sale net receipts"]);
	}
}
