<?php

namespace SHIFT\Trackshift\Test\Upload;

use SHIFT\Trackshift\Upload\BandcampUpload;
use SHIFT\Trackshift\Upload\PRSStatementUpload;
use SHIFT\Trackshift\Upload\UnknownUpload;
use SHIFT\Trackshift\Upload\UploadManager;

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

	public function testLoad_bandcamp():void {
		$tmpFileName = self::getTempFile("bandcamp-simple-3-songs.csv");

		$sut = new UploadManager();
		$statement = $sut->load($tmpFileName);

		self::assertInstanceOf(BandcampUpload::class, $statement->current());
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

	public function testLoadInto():void {
		$tmpFileName1 = self::getTempFile("prs-simple-3-songs.csv");
		$tmpFileName2 = self::getTempFile("prs-simple-3-songs-another-statement.csv");

		$sut = new UploadManager();
		$statement1 = $sut->load($tmpFileName1);
		$statement2 = $sut->loadInto($statement1, $tmpFileName2);

		self::assertSame($statement1, $statement2);
		$i = null;
		foreach($statement1 as $i => $upload) {
			self::assertInstanceOf(PRSStatementUpload::class, $upload);
		}
		self::assertGreaterThan(0, $i);
	}

	public function testPurge():void {
		$dir = dirname(self::getTempFile("prs-simple-3-songs.csv", "purge-test"));
		self::getTempFile("prs-simple-3-songs.csv", "purge-test");
		self::getTempFile("prs-simple-3-songs.csv", "purge-test");

		$sut = new UploadManager();

		$expiredTime = strtotime("-4 weeks");
		touch($dir, $expiredTime);

		self::assertSame(3, $sut->purge(dirname($dir)));
	}
}
