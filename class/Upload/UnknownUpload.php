<?php
namespace SHIFT\Trackshift\Upload;

use SHIFT\Trackshift\Royalty\Money;

class UnknownUpload extends Upload {
	protected function processUsages():void {}

	// phpcs:ignore
	public function extractArtistName(array $row):string {
		return "";
	}

	// phpcs:ignore
	public function extractProductTitle(array $row):string {
		return "";
	}

	// phpcs:ignore
	public function extractEarning(array $row):Money {
		return new Money(0);
	}
}
