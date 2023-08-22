<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class PRSStatementUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["Record Number", "CAE Number", "Work Title", "Amount (performance revenue)", "IP1"];

	public function extractArtistName(array $row): string {
		return $row["IP1"];
	}

	public function extractProductTitle(array $row): string {
		return $row["Work Title"];
	}

	public function extractEarning(array $row): Money {
		return new Money((float)$row["Amount (performance revenue)"]);
	}
}
