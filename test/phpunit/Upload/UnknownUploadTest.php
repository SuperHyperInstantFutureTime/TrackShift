<?php
namespace SHIFT\Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Upload\UnknownUpload;

class UnknownUploadTest extends TestCase {
	public function testExtractArtistName():void {
		$sut = new UnknownUpload("test-id", "test/files/gubbins.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		self::assertSame("Unknown", $sut->extractArtistName($dataRows));
	}

	public function testExtractProductTitle():void {
		$sut = new UnknownUpload("test-id", "test/files/gubbins.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		self::assertSame("Unknown", $sut->extractProductTitle($dataRows));
	}

	public function testExtractEarning():void {
		$sut = new UnknownUpload("test-id", "test/files/gubbins.txt");
		$dataRows = iterator_to_array($sut->generateDataRows());
		self::assertSame(0.0, $sut->extractEarning($dataRows)->value);
	}
}
