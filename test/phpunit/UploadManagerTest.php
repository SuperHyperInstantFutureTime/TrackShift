<?php

use PHPUnit\Framework\TestCase;
use Trackshift\Upload\UploadManager;

class UploadManagerTest extends TestCase {
	/** Load a file with content that isn't recognised by Trackshift. */
	public function testLoad_unknownFileType():void {
		$fileContent = <<<DATA
		name,town
		Greg,Oakwood
		Richard,Ambergate
		DATA;

		$tmpFileName = self::getTempFile($fileContent);

		$sut = new UploadManager();
		$upload = $sut->load($tmpFileName);

		self::assertInstanceOf(UnknownUpload::class, $upload);
	}

	/** @return string absolute file path of the temp file */
	private static function getTempFile($content):string {
		$filePath = sys_get_temp_dir() . "/" . uniqid("trackshift-test-");
		file_put_contents($filePath, $content);
		return $filePath;
	}
}
