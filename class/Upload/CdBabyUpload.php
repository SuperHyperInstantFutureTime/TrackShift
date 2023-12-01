<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;

class CdBabyUpload extends Upload {
	const KNOWN_COLUMNS = ["Report Date", "Quantity", "Isrc", "CDBabySku"];

	protected string $dataRowCsvSeparator = "\t";

	public function extractArtistName(array $row): string {
		return $row["Artist Name"];
	}

	public function extractProductTitle(array $row): string {
		return $row["Track Name"];
	}

	public function extractEarning(array $row): Money {
		return new Money((float)$row["Subtotal"]);
	}
}
