<?php
namespace SHIFT\Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Upload\CargoUpload;

class CargoUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new CargoUpload("test-id", "test/files/cargo-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Person 1", $artistName);
	}

	public function testExtractProductName():void {
		$sut = new CargoUpload("test-id", "test/files/cargo-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productName = $sut->extractProductName($dataRows[0]);
		self::assertSame("Album 1", $productName);
	}

	public function testExtractEarning():void {
		$sut = new CargoUpload("test-id", "test/files/cargo-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(0.00164130103450794, $earning->value);
	}
}
