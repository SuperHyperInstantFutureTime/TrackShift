<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;

class BandcampUpload extends Upload {
	const KNOWN_COLUMNS = ["item type", "item name", "artist", "bandcamp transaction id"];

	protected string $dataRowCsvSeparator = ",";

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
