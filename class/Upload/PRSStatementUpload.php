<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class PRSStatementUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["Record Number", "CAE Number", "Work Title", "Amount (performance revenue)"];

	protected function processUsages():void {
		$headerRow = null;

		while(!$this->file->eof()) {
			$line = $this->file->fgetcsv();
			$row = $this->stripNullBytes($line);
			if(empty($row) || !$row[0]) {
				continue;
			}

			if(!$headerRow) {
				$headerRow = $row;
				continue;
			}

			$data = $this->rowToData($headerRow, $row);
			$workTitle = $data["Work Title"];
			$artist = $this->getArtist($data["CAE Number"]);

			if(!in_array($artist, $this->artistList)) {
				array_push($this->artistList, $artist);
			}

			$this->usageList->add(new Usage(
				$workTitle,
				new Money((float)$data["Amount (performance revenue)"]),
				$artist,
			));
		}
	}
}
