<?php
namespace SHIFT\Trackshift\Test\Usage;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Usage\ArtistUsage;
use SHIFT\Trackshift\Usage\Usage;

class ArtistUsageTest extends TestCase {
	public function testGetAllArtists_empty():void {
		$sut = new ArtistUsage();
		self::assertEmpty($sut->getAllArtists());
	}

	public function testGetAllArtists():void {
		$sut = new ArtistUsage();
		$artist = self::getMockBuilder(Artist::class)
			->setConstructorArgs(["1", "Artist 1"])
			->getMock();
		$sut->addArtistUsage(
			$artist,
			self::createMock(Usage::class),
		);
		self::assertCount(1, $sut->getAllArtists());
	}

	public function testGetUsageListForArtist_doesNotExist():void {
		$sut = new ArtistUsage();
		$artist = self::getMockBuilder(Artist::class)
			->setConstructorArgs(["1", "Artist 1"])
			->getMock();
		$usageList = $sut->getUsageListForArtist($artist);
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
