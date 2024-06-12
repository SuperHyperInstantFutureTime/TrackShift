<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Money;

class CdBabyUpload extends Upload {
	const KNOWN_COLUMNS = ["Report Date", "Quantity", "Isrc", "CDBabySku"];

	protected string $dataRowCsvSeparator = "\t";

	public function extractArtistName(array $row): string {
		return $row["Artist Name"];
	}

	public function extractProductTitle(array $row): string {
		return $row["Album Name"];
	}

	public function extractEarning(array $row): Money {
		return new Money((float)$row["Subtotal"]);
	}

	public function extractEarningDate(array $row):DateTime {
		// Expected format: 10/15/2023 12:00:00 AM
		$dateString = $row["Report Date"];
		return DateTime::createFromFormat("m/d/Y H:i:s a", $dateString);
	}
}
