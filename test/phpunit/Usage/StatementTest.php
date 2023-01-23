<?php
namespace Trackshift\Test\Usage;

use PHPUnit\Framework\TestCase;
use Trackshift\Royalty\Money;
use Trackshift\Upload\Upload;
use Trackshift\Usage\Aggregation;
use Trackshift\Usage\Statement;

class StatementTest extends TestCase {
	public function testCount_empty():void {
		$sut = new Statement();
		self::assertCount(0, $sut);
	}

	public function testCount():void {
		$sut = new Statement();
		$expectedCount = rand(3, 99);
		for($i = 0; $i < $expectedCount; $i++) {
			$upload = self::createMock(Upload::class);
			$sut->addUpload($upload);
		}

		self::assertCount($expectedCount, $sut);
	}

	public function testClear():void {
		$sut = new Statement();
		$upload = self::createMock(Upload::class);
		$sut->addUpload($upload);
		$sut->addUpload($upload);
		$sut->addUpload($upload);

		self::assertCount(3, $sut);
		$sut->clear();
		self::assertCount(0, $sut);
	}

	public function testGetAggregatedUsages_empty():void {
		$sut = new Statement();
		$aggregation = $sut->getAggregatedUsages("test");
		self::assertSame(0.00, $aggregation->getTotalValue()->value);
	}

	public function testGetAggregatedUsages():void {
		$mockMoney = self::getMockBuilder(Money::class)
			->setConstructorArgs([1.23])
			->getMock();

		$usageAggregation = self::createMock(Aggregation::class);
		$usageAggregation->method("getTotalValue")
			->willReturn($mockMoney);

		$upload = self::createMock(Upload::class);
		$upload->method("getAggregatedUsages")
			->willReturn($usageAggregation);

		$sut = new Statement();
		$sut->addUpload($upload);

		$aggregation = $sut->getAggregatedUsages("test");
		self::assertSame(0.00, $aggregation->getTotalValue()->value);
	}

	public function testIsMultipleArtist_allUploadsSingleArtist():void {
		$upload = self::createMock(Upload::class);
		$upload->method("isMultipleArtist")
			->willReturn(false);

		$sut = new Statement();
		$sut->addUpload($upload);
		$sut->addUpload($upload);
		$sut->addUpload($upload);

		self::assertFalse($sut->isMultipleArtist());
	}

	public function testIsMultipleArtist_oneUploadOtherArtist():void {
		$upload = self::createMock(Upload::class);
		$upload->method("isMultipleArtist")
			->willReturn(false);
		$uploadMultiple = self::createMock(Upload::class);
		$uploadMultiple->method("isMultipleArtist")
			->willReturn(true);

		$sut = new Statement();
		$sut->addUpload($upload);
		$sut->addUpload($uploadMultiple);
		$sut->addUpload($upload);
		$sut->addUpload($upload);

		self::assertTrue($sut->isMultipleArtist());
	}
}
