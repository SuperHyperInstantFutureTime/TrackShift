<?php
namespace SHIFT\TrackShift\Upload;

use SHIFT\TrackShift\Royalty\Money;
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
		return new Money((float)$row["Reported Royalty"]);
	}

	public function openFile() {
		$glob = UploadRepository::MATCHING_FILE_LIST[CargoDigitalUpload::class];

	}
}
