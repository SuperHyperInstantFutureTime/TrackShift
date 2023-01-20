<?php
namespace Trackshift\Test\Usage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Trackshift\Artist\Artist;
use Trackshift\Usage\ArtistUsage;
use Trackshift\Usage\Usage;

class ArtistUsageTest extends TestCase {
	public function testGetAllArtists_empty():void {
		$sut = new ArtistUsage();
		self::assertEmpty($sut->getAllArtists());
	}

	public function testGetAllArtists():void {
		$sut = new ArtistUsage();
		$sut->addArtistUsage(
			self::createMock(Artist::class),
			self::createMock(Usage::class),
		);
		self::assertCount(1, $sut->getAllArtists());
	}

	public function testGetUsageListForArtist_doesNotExist():void {
		$sut = new ArtistUsage();
		$usageList = $sut->getUsageListForArtist(self::createMock(Artist::class));
		self::assertCount(0, $usageList);
	}

	public function testGetUsageListForArtist():void {
		$sut = new ArtistUsage();
		/** @var MockObject|Artist $artist1 */
		$artist1 = self::getMockBuilder(Artist::class)
			->setConstructorArgs(["1", "Artist 1"])
			->getMock();
		/** @var MockObject|Artist $artist2 */
		$artist2 = self::getMockBuilder(Artist::class)
			->setConstructorArgs(["2", "Artist 2"])
			->getMock();

		$sut->addArtistUsage($artist1, self::createMock(Usage::class));
		$sut->addArtistUsage($artist1, self::createMock(Usage::class));
		$sut->addArtistUsage($artist1, self::createMock(Usage::class));

		$sut->addArtistUsage($artist2, self::createMock(Usage::class));
		$sut->addArtistUsage($artist2, self::createMock(Usage::class));

		self::assertCount(3, $sut->getUsageListForArtist($artist1));
		self::assertCount(2, $sut->getUsageListForArtist($artist2));
	}
}
