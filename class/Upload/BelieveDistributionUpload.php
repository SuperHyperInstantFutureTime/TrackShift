<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;

class BelieveDistributionUpload extends Upload {
	const KNOWN_COLUMNS = ["Release Catalog nb", "Reporting month", "Client Payment Currency"];

	public function extractArtistName(array $row):string {
		return $row["Artist Name"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Track title"];
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["Net Revenue"]);
	}
}
