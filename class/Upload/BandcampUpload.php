<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;

class BandcampUpload extends Upload {
	const KNOWN_CSV_COLUMNS = ["item type", "item name", "artist", "bandcamp transaction id"];

	protected function processUsages():void {
		$headerRow = null;

		while(!$this->file->eof()) {
			$row = $this->stripNullBytes($this->file->fgetcsv());
			if(empty($row) || !$row[0]) {
				continue;
			}

			if(!$headerRow) {
				$headerRow = $row;
				continue;
			}


			$data = $this->rowToData($headerRow, $row);
			if(empty($data["net amount"])) {
// TODO: Talk to Biff about this - I think skipping "pay out" rows is OK, but need to go over the data.
				continue;
			}

			$workTitle = $data["item name"];
			$artist = $this->getArtist($data["artist"]);

			if(!in_array($artist, $this->artistList)) {
				array_push($this->artistList, $artist);
			}

			$this->usageList->add(new Usage(
				$workTitle,
				new Money((float)$data["net amount"]),
				$artist,
			));
		}
	}
}
