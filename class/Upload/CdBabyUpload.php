<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;

class CdBabyUpload extends Upload {
	const CURRENCY_OVERRIDE = Currency::USD->name;
	const KNOWN_COLUMNS = ["Report Date", "Quantity", "Isrc", "CDBabySku"];

	protected string $dataRowCsvSeparator = "\t";

	public function extractArtistName(array $row): string {
		return $row["Artist Name"];
	}

	public function extractProductTitle(array $row): string {
		return $row["Album Name"];
	}

	public function extractEarning(array $row): Money {
		return new Money(
			(float)$row["Subtotal"],
			Currency::USD,
		);
	}

	public function extractEarningDate(array $row):DateTime {
		// Expected format: 10/15/2023 12:00:00 AM
		$dateString = $row["Report Date"];
		return date_create_from_format("m/d/Y H:i:s a", $dateString);
	}
}
