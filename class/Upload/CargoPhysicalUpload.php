<?php
namespace SHIFT\TrackShift\Upload;

use OpenSpout\Reader\XLSX\Reader;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Usage\Usage;
use Generator;

class CargoPhysicalUpload extends Upload {
	const KNOWN_COLUMNS = ["Period", "Catalogue No.", "Label", "Label ID", "Sold To"];

	public function extractArtistName(array $row):string {
		return $row["Artist / Invoice Ref"];
	}

	public function extractProductTitle(array $row):string {
		return $row["Title / More info"];
	}

	public function extractEarning(array $row):Money {
		$value = str_replace(["(", ")"], "", $row["Net after fee"]);
		return new Money((float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT , FILTER_FLAG_ALLOW_FRACTION ));
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
