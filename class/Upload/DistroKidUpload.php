<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Repository\StringCleaner;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\UsageRepository;

/**
 * DistroKid uploads have a few strange rules. We always want a product to
 * relate to the sold album, but most of the usages within DistroKid are per
 * track. This is OK, because there's a UPC field (which relates directly to
 * an album), but more often than not, the UPC field is left empty.
 *
 * Good news though: if the track has no UPC, there will always be another
 * record that does. Maybe the UPC is only written to the CSV for the first
 * stream? Whatever the reason, this rule is always the case in the data I've
 * analysed.
 *
 * So the rules are as follows:
 * 1) If it's a usage of an album (in the "Song/Album" field), the data is
 * already correct, but we keep reference of the UPC for when we need it later.
 * 2) If it's a usage of a song, and we know the UPC, we name the product with
 * a special syntax: ::UNSORTED_UPC::12345 - where 12345 is the UPC value.
 * 3) If we don't know the UPC, we use the track's code and name the product
 * with a different special syntax: ::UNSORTED_ISRC::abcdef - where abcdef is
 * the track's ISRC code.
 *
 * Then, the UsageRepositry::process() function will know to look for this
 * special syntax, perform two additional steps:
 *
 * 1) If there is an ::UNSORTED_UPC::, it'll use the retained record of ISRC
 * codes to look up the correct UPC and rename the product accordingly.
 * 2) If there's a ::UNSORTED_ISRC::, it'll use Spotify's API to look up the
 * product by UPC.
 */
class DistroKidUpload extends Upload {
	const KNOWN_COLUMNS = ["Reporting Date", "Sale Month", "Store", "Artist", "Title", "ISRC", "UPC", "Song/Album"];

	protected string $dataRowCsvSeparator = "\t";

	/** @param array<string, string> $row */
	public function loadUsageForInternalLookup(array $row):void {
		$upc = trim($row["UPC"] ?? "");
		$isrc = trim($row["ISRC"] ?? "");
		$title = trim($row["Title"] ?? "");

		if($isrc && $upc) {
			$this->isrcUpcMap[$isrc] = $upc;
		}

		if($row["Song/Album"] === "Album") {
			$this->upcProductTitleMap[$upc] = new StringCleaner($title);
		}
	}

	public function extractArtistName(array $row):string {
		return $row["Artist"];
	}

	public function extractProductTitle(array $row):string {
		$upc = trim($row["UPC"] ?? "");
		$isrc = trim($row["ISRC"] ?? "");

		if(isset($this->upcProductTitleMap[$upc])) {
			return $this->upcProductTitleMap[$upc];
		}

		if(!$upc) {
			$upc = $this->getUpc($isrc, $upc);
		}

		if($upc) {
			return UsageRepository::UNSORTED_UPC . $upc;
		}

		return UsageRepository::UNSORTED_ISRC . $isrc;
	}

	public function extractEarning(array $row):Money {
		return new Money((float)$row["Earnings (USD)"]);
	}

	public function extractEarningDate(array $row):DateTime {
		return new DateTime($row["Reporting Date"]);
	}

	/**
	 * @param string $upc
	 * @param string $title
	 * @return string
	 */
	protected function extractProductTitleForAlbum(string $upc, string $title):string {
		if($upc) {
			$this->upcProductTitleMap[$upc] = $title;
		}

		return $title;
	}

	/**
	 * @param string $isrc
	 * @param string|null $upc
	 * @return string|null
	 */
	protected function getUpc(string $isrc, ?string $upc):?string {
		if($isrc) {
			$upc = $this->isrcUpcMap[$isrc] ?? null;
		}
		return $upc;
	}
}
