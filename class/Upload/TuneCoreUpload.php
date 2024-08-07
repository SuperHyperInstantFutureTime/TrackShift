<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;

class TuneCoreUpload extends Upload {
	const CURRENCY_OVERRIDE = Currency::USD->name;
	const KNOWN_COLUMNS = ["TC Song ID", "Optional ISRC", "Optional UPC"];

	public function extractArtistName(array $row):string {
		return $row["Artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Release Title"];
	}

	public function extractEarning(array $row):Money {
		return new Money(
			(float)$row["Total Earned"],
			Currency::fromCode($row["Currency"]),
		);
	}

	public function extractEarningDate(array $row):DateTime {
		return new DateTime($row["Posted Date"]);
	}
}
