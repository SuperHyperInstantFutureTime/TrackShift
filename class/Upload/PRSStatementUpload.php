<?php
namespace Trackshift\Upload;

use Trackshift\Royalty\Money;
use Trackshift\Usage\Usage;

class PRSStatementUpload extends Upload {
	protected function processUsages():void {
		$headerRow = null;

		while(!$this->file->eof()) {
			$row = $this->file->fgetcsv();
			if(empty($row) || is_null($row[0])) {
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
