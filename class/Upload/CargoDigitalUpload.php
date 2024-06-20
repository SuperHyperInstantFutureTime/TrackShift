<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\TrackShiftException;
use SHIFT\TrackShift\Usage\Usage;

class CargoDigitalUpload extends Upload {
	const KNOWN_COLUMNS = ["Royalty ID", "Asset ISRC", "Reported Royalty"];

	public function extractArtistName(array $row):string {
		return $row["Asset Artist"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Product Title"];
	}

	public function extractEarning(array $row):Money {
		return new Money(
			(float)$row["Reported Royalty"],
			Currency::fromCode($row["Currency"]),
		);
	}

	public function extractEarningDate(array $row):DateTime {
		$timestamp = $row["Statement Run Name"];
		$matchSuccess = preg_match("/(?P<YEAR>\d{4})Q(?P<QUARTER>\d)/", $timestamp, $matches);
		if(!$matchSuccess) {
			throw new TrackShiftException("Cargo Digital earning date does not match: $timestamp");
		}

		$monthOfNextQuarter = match((int)$matches["QUARTER"]) {
			1 => 4,
			2 => 7,
			3 => 10,
			default => 1,
		};

		$year = $matches["YEAR"];
		if($monthOfNextQuarter === 1) {
			$year++;
		}

		return new DateTime("$year-$monthOfNextQuarter-01");
	}

	public function openFile() {
		if(pathinfo($this->filePath, PATHINFO_EXTENSION) === "zip") {
			$this->filePath = new ZipFileFinder($this->filePath);
		}

		return parent::openFile();
	}
}
