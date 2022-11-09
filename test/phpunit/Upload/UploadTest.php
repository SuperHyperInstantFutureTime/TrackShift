<?php
namespace Trackshift\Test\Upload;

use Trackshift\Upload\UnknownUpload;

class UploadTest extends UploadTestCase {
	public function testDelete():void {
		$tempFilePath = self::getTempFile("gubbins.txt");
		$sut = new UnknownUpload($tempFilePath);
		self::assertFileExists($tempFilePath);
		$sut->delete();
		self::assertFileDoesNotExist($tempFilePath);
	}
}
