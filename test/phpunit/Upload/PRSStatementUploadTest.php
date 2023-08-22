<?php
namespace SHIFT\Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Upload\PRSStatementUpload;

class PRSStatementUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new PRSStatementUpload("test-id", "test/files/prs-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$artistName = $sut->extractArtistName($dataRows[0]);
		self::assertSame("Person 1", $artistName);
	}

	public function testExtractProductName():void {
		$sut = new PRSStatementUpload("test-id", "test/files/prs-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$productName = $sut->extractProductTitle($dataRows[0]);
		self::assertSame("Song 1", $productName);
	}

	public function testExtractEarning():void {
		$sut = new PRSStatementUpload("test-id", "test/files/prs-simple-3-songs.csv");
		$dataRows = iterator_to_array($sut->generateDataRows());
		$earning = $sut->extractEarning($dataRows[0]);
		self::assertSame(0.016, $earning->value);
	}
}
