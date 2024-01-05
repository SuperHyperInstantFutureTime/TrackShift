<?php
namespace SHIFT\TrackShift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\TrackShift\Upload\DistroKidUpload;

class DistroKidUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new DistroKidUpload("test-id", "test/files/DistroKid_Test.tsv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Artist 1", $artistName);
	}

	public function testExtractProductTitle():void {
		$sut = new DistroKidUpload("test-id", "test/files/DistroKid_Test.tsv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productTitle = $sut->extractProductTitle($dataRows[0]);
		self::assertSame("::UNSORTED_UPC::111111111111", $productTitle);
	}

	public function testExtractEarning():void {
		$sut = new DistroKidUpload("test-id", "test/files/DistroKid_Test.tsv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(0.00789993, $earning->value);
	}
}
