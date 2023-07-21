<?php
namespace SHIFT\Trackshift\Test\Upload;

use SHIFT\Trackshift\Upload\UnknownUpload;

class UploadTest extends UploadTestCase {
	public function testDelete():void {
		$tempFilePath = self::getTempFile("gubbins.txt");
		$sut = new UnknownUpload($tempFilePath);
		self::assertFileExists($tempFilePath);
		$sut->delete();
		self::assertFileDoesNotExist($tempFilePath);
	}
}
