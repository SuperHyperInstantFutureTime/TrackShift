<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;

class DistroKidUpload extends Upload {
	const KNOWN_COLUMNS = ["Reporting Date", "Sale Month", "Store", "Artist", "Title"];

	protected string $dataRowCsvSeparator = "\t";

	public function extractArtistName(array $row):string {
		return $row["Artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Title"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["Earnings (USD)"]);
	}
}
