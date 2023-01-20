<?php
namespace Trackshift\Test\Upload;

use Trackshift\Upload\BandcampUpload;
use Trackshift\Upload\PRSStatementUpload;

class BandcampUploadTest extends UploadTestCase {
	public function testGetUsageTotal():void {
		$tmpFile = self::getTempFile("bandcamp-simple-3-songs.csv");
		$sut = new BandcampUpload($tmpFile);
		$moneyTotalUsage = $sut->getUsageTotal();

		self::assertSame(17.68, $moneyTotalUsage->value);
		self::assertSame("£17.68", (string)$moneyTotalUsage);
	}

	public function testIsMultipleArtist_no():void {
		$tmpFile = self::getTempFile("bandcamp-simple-3-songs.csv");
		$sut = new BandcampUpload($tmpFile);
		self::assertFalse($sut->isMultipleArtist());
	}

	public function testIsMultipleArtist_yes():void {
		$tmpFile = self::getTempFile("bandcamp-simple-multiple-artist.csv");
		$sut = new BandcampUpload($tmpFile);
		self::assertTrue($sut->isMultipleArtist());
	}
}
