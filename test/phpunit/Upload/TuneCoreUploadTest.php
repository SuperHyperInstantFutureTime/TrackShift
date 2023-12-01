<?php
namespace SHIFT\TrackShift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\TrackShift\Upload\TuneCoreUpload;

class TuneCoreUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new TuneCoreUpload("test-id", "test/files/Tunecore_Test.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Artist 1", $artistName);
	}

	public function testExtractProductTitle():void {
		$sut = new TuneCoreUpload("test-id", "test/files/Tunecore_Test.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productTitle = $sut->extractProductTitle($dataRows[0]);
		self::assertSame("Song 1", $productTitle);
	}

	public function testExtractEarning():void {
		$sut = new TuneCoreUpload("test-id", "test/files/Tunecore_Test.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(0.0271, $earning->value);
	}
}
