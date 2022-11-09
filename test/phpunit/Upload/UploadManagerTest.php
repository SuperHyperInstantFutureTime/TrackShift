<?php

namespace Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use Trackshift\Upload\PRSStatementUpload;
use Trackshift\Upload\UnknownUpload;
use Trackshift\Upload\UploadManager;

class UploadManagerTest extends UploadTestCase {
	/** Load a file with content that isn't recognised by Trackshift. */
	public function testLoad_unknownFileType():void {
		$tmpFileName = self::getTempFile("gubbins.txt");

		$sut = new UploadManager();
		$upload = $sut->load($tmpFileName);

		self::assertInstanceOf(UnknownUpload::class, $upload);
	}

	public function testLoad_prsStatement():void {
		$tmpFileName = self::getTempFile("prs-simple-3-songs.csv");

		$sut = new UploadManager();
		$upload = $sut->load($tmpFileName);

		self::assertInstanceOf(PRSStatementUpload::class, $upload);
	}
}
