<?php
namespace Trackshift\Test\Upload;

use Trackshift\Upload\BandcampUpload;

class BandcampUploadTest extends UploadTestCase {
	public function testGetUsageTotal():void {
		$tmpFile = self::getTempFile("bandcamp-simple-3-songs.csv");
		$sut = new BandcampUpload($tmpFile);
		$moneyTotalUsage = $sut->getUsageTotal();

		self::assertSame(17.68, $moneyTotalUsage->value);
		self::assertSame("£17.68", (string)$moneyTotalUsage);
	}
}
