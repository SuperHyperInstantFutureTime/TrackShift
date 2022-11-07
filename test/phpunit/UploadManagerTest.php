<?php

use PHPUnit\Framework\TestCase;
use Trackshift\Upload\PRSStatementUpload;
use Trackshift\Upload\UnknownUpload;
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

	public function testLoad_prsStatement():void {
		$fileContent = <<<DATA
		Record Number,CAE Number,Rights Type,Distribution (posted),Usage Narrative,Usage Summary,Un-Notified Flag,Work Title,Amount (performance revenue),Share,Perf Start Date,Perf End Date,Prod Header,Broadcast Region,Production ID,Number of Perfs,Duration,IP1,IP2,IP3,IP4,Tunecode,ISWC,WID,Catalogue Number,Invoice Number,Currency,Works Share Transfer From,Works Share Transfer To,Old Share,New Share,Adjustment Reason
		1,105,P,2022041,Super Stream Service,Online, ,Complete Saturation,0.0016,100,01/03/2019,31/03/2019, , ,,1, ,Greg Bowler, , , ,105GB,t1234567890,105GB, ,1050105,GBP, , , , , 
		DATA;
		$tmpFileName = self::getTempFile($fileContent);

		$sut = new UploadManager();
		$upload = $sut->load($tmpFileName);

		self::assertInstanceOf(PRSStatementUpload::class, $upload);
	}

	/** @return string absolute file path of the temp file */
	private static function getTempFile($content):string {
		$filePath = sys_get_temp_dir() . "/" . uniqid("trackshift-test-");
		file_put_contents($filePath, $content);
		return $filePath;
	}
}
