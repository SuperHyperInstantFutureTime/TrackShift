<?php
namespace SHIFT\TrackShift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\TrackShift\Upload\CargoDigitalUpload;
use SHIFT\TrackShift\Upload\CdBabyUpload;

class CdBabyUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new CdBabyUpload("test-id", "test/files/CdBaby_Test.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Artist 1", $artistName);
	}

	public function testExtractProductName():void {
		$sut = new CdBabyUpload("test-id", "test/files/CdBaby_Test.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productName = $sut->extractProductTitle($dataRows[0]);
		self::assertSame("Track 1", $productName);
	}

	public function testExtractEarning():void {
		$sut = new CdBabyUpload("test-id", "test/files/CdBaby_Test.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(0.0998269974, $earning->value);
	}
}
