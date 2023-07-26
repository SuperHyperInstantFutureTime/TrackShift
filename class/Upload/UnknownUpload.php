<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;

class UnknownUpload extends Upload {
	protected function processUsages():void {}

	public function extractArtistName(array $row): string {
		return "";
	}

	public function extractProductName(array $row): string {
		return "";
	}

	public function extractEarning(array $row): Money {
		return new Money(0);
	}
}
