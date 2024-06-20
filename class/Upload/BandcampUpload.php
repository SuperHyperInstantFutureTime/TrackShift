<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
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

	public function extractEarningDate(array $row):DateTime {
		preg_match("/(?<DATE_STRING>[^\s$]+)\s?.*$/", $row["date"], $matches);
		return date_create_from_format("d/m/y", $matches["DATE_STRING"]);
	}
}
