<?php

namespace Trackshift\Test\Upload;

use Trackshift\Upload\PRSStatementUpload;
use Trackshift\Upload\UnknownUpload;
use Trackshift\Upload\UploadManager;

class UploadManagerTest extends UploadTestCase {
	/** Load a file with content that isn't recognised by Trackshift. */
	public function testLoad_unknownFileType():void {
		$tmpFileName = self::getTempFile("gubbins.txt");

		$sut = new UploadManager();
		$statement = $sut->load($tmpFileName);

		self::assertInstanceOf(UnknownUpload::class, $statement->current());
	}

	public function testLoad_prsStatement():void {
		$tmpFileName = self::getTempFile("prs-simple-3-songs.csv");

		$sut = new UploadManager();
		$statement = $sut->load($tmpFileName);

		self::assertInstanceOf(PRSStatementUpload::class, $statement->current());
	}

	public function testLoad_multiplePrsStatements():void {
		$tmpFileName1 = self::getTempFile("prs-simple-3-songs.csv");
		$tmpFileName2 = self::getTempFile("prs-simple-3-songs-another-statement.csv");

		$sut = new UploadManager();
		$statement = $sut->load($tmpFileName1, $tmpFileName2);

		$i = null;
		foreach($statement as $i => $upload) {
			self::assertInstanceOf(PRSStatementUpload::class, $upload);
		}
		self::assertGreaterThan(0, $i);
	}
}
