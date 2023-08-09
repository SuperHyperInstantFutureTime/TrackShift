<?php
namespace SHIFT\Trackshift\Test\Upload;

use DateTime;
use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Upload\BandcampUpload;

class BandcampUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new BandcampUpload("test-id", "test/files/bandcamp-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Person 1", $artistName);
	}

	public function testExtractProductName():void {
		$sut = new BandcampUpload("test-id", "test/files/bandcamp-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productName = $sut->extractProductName($dataRows[0]);
		self::assertSame("BC 1", $productName);
	}

	public function testExtractEarning():void {
		$sut = new BandcampUpload("test-id", "test/files/bandcamp-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(6.22, $earning->value);
	}

	public function testGetXYZ_bindGetters():void {
		$filePath = "test/files/bandcamp-simple-3-songs.csv";
		$sut = new BandcampUpload("test-id", $filePath);
		$actualCreationTime = new DateTime("@" . filectime($filePath));
		self::assertSame($actualCreationTime->format("d/m/Y"), $sut->getCreatedAtFormattedDate());
		self::assertSame($actualCreationTime->format("H:i"), $sut->getUploadedAtFormattedTime());
	}
}
