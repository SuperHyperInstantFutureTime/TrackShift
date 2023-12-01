<?php
namespace SHIFT\TrackShift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\TrackShift\Upload\CargoDigitalUpload;
use SHIFT\TrackShift\Upload\CargoPhysicalUpload;

class CargoPhysicalUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new CargoPhysicalUpload("test-id", "test/files/Cargo_Physical_Test.xlsx");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("ARTIST 1", $artistName);
	}

	public function testExtractProductName():void {
		$sut = new CargoPhysicalUpload("test-id", "test/files/Cargo_Physical_Test.xlsx");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productName = $sut->extractProductTitle($dataRows[0]);
		self::assertSame("ALBUM 1", $productName);
	}

	public function testExtractEarning():void {
		$sut = new CargoPhysicalUpload("test-id", "test/files/Cargo_Physical_Test.xlsx");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(7.28, $earning->value);
	}
}
