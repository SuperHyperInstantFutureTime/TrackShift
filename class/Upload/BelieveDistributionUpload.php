<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\NotYetImplementedException;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;

class BelieveDistributionUpload extends Upload {
	const CURRENCY_COLUMN = "Client Payment Currency";
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


	public function extractEarningDate(array $row):DateTime { // phpcs:ignore
		throw new NotYetImplementedException("Believe distributions do not have an earning date extractor yet");
	}

	public function getDefaultCurrency():Currency {

	}
}
