<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;

class CargoPhysicalUpload extends Upload {
	const KNOWN_COLUMNS = ["Period", "Catalogue No.", "Label", "Label ID", "Sold To"];

	public function extractArtistName(array $row):string {
		return $row["Artist / Invoice Ref"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Title / More info"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)filter_var($row["Net after fee"], FILTER_SANITIZE_NUMBER_FLOAT , FILTER_FLAG_ALLOW_FRACTION ));
	}
}
