<?php
namespace SHIFT\Trackshift\Test\Usage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Aggregation;
use SHIFT\Trackshift\Usage\Usage;

class AggregationTest extends TestCase {
	public function testIterator():void {
		$sut = new Aggregation();

		$mockTitleList = ["Song 1", "Song 2", "Song 3"];
		$mockUsageList = [];

		/** @var Money $mockMoney */
		$mockMoney = self::getMockBuilder(Money::class)
			->setConstructorArgs([1])
			->getMock();

		foreach($mockTitleList as $title) {
			/** @var Usage|MockObject $mockUsage */
			$mockUsage = self::getMockBuilder(Usage::class)
				->setConstructorArgs([$title, $mockMoney])
				->getMock();
			array_push($mockUsageList, $mockUsage);
		}

		$sut->add($mockTitleList[0], $mockUsageList[0]);
		$sut->add($mockTitleList[0], $mockUsageList[0]);
		$sut->add($mockTitleList[0], $mockUsageList[0]);
		$sut->add($mockTitleList[0], $mockUsageList[0]);

		$sut->add($mockTitleList[1], $mockUsageList[1]);
		$sut->add($mockTitleList[1], $mockUsageList[1]);
		$sut->add($mockTitleList[1], $mockUsageList[1]);

		$sut->add($mockTitleList[2], $mockUsageList[2]);

		$actualValueList = [];
		foreach($mockTitleList as $title) {
			$actualValueList[$title] = 0;
		}

		foreach($sut as $title => $usageList) {
			foreach($usageList as $usage) {
				$actualValueList[$title] += $usage->amount->value;
			}
		}

		self::assertSame(4.0, $actualValueList[$mockTitleList[0]]);
		self::assertSame(3.0, $actualValueList[$mockTitleList[1]]);
		self::assertSame(1.0, $actualValueList[$mockTitleList[2]]);
	}
}
