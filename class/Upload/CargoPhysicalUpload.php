<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use OpenSpout\Reader\XLSX\Reader;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\TrackShiftException;
use SHIFT\TrackShift\Usage\Usage;
use Generator;

class CargoPhysicalUpload extends Upload {
	const CURRENCY_OVERRIDE = Currency::GBP->name;
	const KNOWN_COLUMNS = ["Period", "Catalogue No.", "Label", "Label ID", "Sold To"];

	public function extractArtistName(array $row):string {
		return $row["Artist / Invoice Ref"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Title / More info"];
	}

	public function extractEarning(array $row):Money {
		$value = str_replace(["(", ")"], "", $row["Net after fee"]);
		return new Money(
			(float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ),
			Currency::GBP,
		);
	}

	public function extractEarningDate(array $row):DateTime {
		$timestamp = $row["Period"];
		$matchSuccess = preg_match("/(?P<YEAR>\d{4})_(?P<MONTH_NUM>\d+)/", $timestamp, $matches);
		if(!$matchSuccess) {
			throw new TrackShiftException("Cargo Physical earning date does not match: $timestamp");
		}

		$monthOfNextQuarter = match((int)$matches["MONTH_NUM"]) {
			1, 2, 3 => 4,
			4, 5, 6 => 8,
			7, 8, 9 => 10,
			default => 1,
		};

		$year = $matches["YEAR"];
		if($monthOfNextQuarter === 1) {
			$year++;
		}

		return new DateTime("$year-$monthOfNextQuarter-01");
	}

	public function generateDataRows():Generator {
		$headerRow = null;

		$reader = new Reader();
		$reader->open($this->filePath);
		$sheet = $reader->getSheetIterator()->current();
		foreach($sheet->getRowIterator() as $excelRow) {
			$row = $excelRow->toArray();
			if(!$row[0]) {
				continue;
			}

			if(!$headerRow) {
				$headerRow = $row;
				continue;
			}

			yield $this->rowToData($headerRow, $row);
		}
	}
}
