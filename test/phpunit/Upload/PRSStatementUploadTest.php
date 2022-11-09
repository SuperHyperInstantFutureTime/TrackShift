<?php
namespace Trackshift\Test\Upload;

use Trackshift\Upload\PRSStatementUpload;
use Trackshift\Usage\Usage;

class PRSStatementUploadTest extends UploadTestCase {
	public function testGetUsageTotal():void {
		$tmpFile = self::getTempFile("prs-simple-3-songs.csv");
		$sut = new PRSStatementUpload($tmpFile);
		$moneyTotalUsage = $sut->getUsageTotal();

		self::assertSame(0.372, $moneyTotalUsage->value);
		self::assertSame("£0.37", (string)$moneyTotalUsage);
	}

	public function testGetAggregatedUsageTotals():void {
		$tmpFileName = self::getTempFile("prs-simple-3-songs.csv");
		$sut = new PRSStatementUpload($tmpFileName);
		$aggregation = $sut->getAggregatedUsages("workTitle");

		self::assertSame(0.104, $aggregation->getTotalValueForAggregate("Song 1")->value);
		self::assertSame(0.174, $aggregation->getTotalValueForAggregate("Song 2")->value);
		self::assertSame(0.094, $aggregation->getTotalValueForAggregate("Song 3")->value);

		self::assertSame(0.372, $aggregation->getTotalValue()->value);
	}
}
