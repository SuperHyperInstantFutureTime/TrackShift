<?php
namespace Trackshift\Test\Usage;

use PHPUnit\Framework\TestCase;
use Trackshift\Upload\Upload;
use Trackshift\Usage\Statement;

class StatementTest extends TestCase {
	public function testCount_empty():void {
		$sut = new Statement();
		self::assertCount(0, $sut);
	}

	public function testCount():void {
		$sut = new Statement();
		/** @noinspection PhpComposerExtensionStubsInspection */
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
}
