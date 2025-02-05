<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;

class AWALUpload extends Upload {
    const KNOWN_COLUMNS = ["STATEMENT PERIOD", "ACCOUNT ID", "CONTRACT ID", "TRANSACTION DATE", "SALE COUNTRY", "STORE", "SERVICE DETAIL", "LABEL IMPRINT", "PRODUCT ARTIST", "PRODUCT", "PRODUCT VERSION"];
    const CURRENCY_COLUMN = "ACCOUNT CURRENCY";

    public function extractArtistName(array $row):string {
		return $row["PRODUCT ARTIST"];
	}

	public function extractProductTitle(array $row):string {
		return $row["PRODUCT"];
	}

	public function extractEarning(array $row):Money {
		return new Money(
			(float)$row["NET SHARE ACCOUNT CURRENCY"],
			Currency::fromCode($row["ACCOUNT CURRENCY"]),
		);
	}

    public function extractEarningDate(array $row):DateTime {
		return new DateTime($row["TRANSACTION DATE"]);
	}
}