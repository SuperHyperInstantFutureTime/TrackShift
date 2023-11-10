<?php
namespace SHIFT\Trackshift\Test\Upload;

use Gt\Database\Query\QueryCollection;
use Gt\Session\SessionStore;
use PHPUnit\Framework\TestCase;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Upload\UploadRepository;

class UploadRepositoryTest extends TestCase {
	const TMP_BASEDIR = "/tmp/trackshift-phpunit";

	public function tearDown(): void {
		exec("rm -rf " . self::TMP_BASEDIR);
	}

	public function testPurgeOldFiles_none():void {
		$baseTmpDir = self::TMP_BASEDIR . "/upload";
		$tmpDir = "$baseTmpDir/" . uniqid("USER_");
		if(!is_dir($tmpDir)) {
			mkdir($tmpDir, recursive: true);
			touch($tmpDir, strtotime("-10 weeks"));
		}
		for($i = 0; $i < 10; $i++) {
			$tmpFile = "$tmpDir/file-$i.example";
			$mTime = strtotime("-$i days");
			touch($tmpFile, $mTime);
		}

		$userRepository = new UserRepository(self::createMock(QueryCollection::class), self::createMock(SessionStore::class));
		$auditRepository = new AuditRepository(self::createMock(QueryCollection::class), $userRepository);

		$queryCollection = self::createMock(QueryCollection::class);
		$queryCollection->method("delete")
			->with("deleteByFilePath")
			->willReturn(1);
		$sut = new UploadRepository($queryCollection, $auditRepository);
		$numPurged = $sut->purgeOldFiles($baseTmpDir);
		self::assertSame(0, $numPurged);
	}

	public function testPurgeOldFiles():void {
		$baseTmpDir = self::TMP_BASEDIR . "/upload";
		$tmpDir = "$baseTmpDir/" . uniqid("USER_");
		if(!is_dir($tmpDir)) {
			mkdir($tmpDir, recursive: true);
			touch($tmpDir, strtotime("-10 weeks"));
		}
		for($i = 0; $i < 10; $i++) {
			$tmpFile = "$tmpDir/file-$i.example";
			$mTime = strtotime("-$i weeks");
			touch($tmpFile, $mTime);
		}

		$userRepository = new UserRepository(self::createMock(QueryCollection::class), self::createMock(SessionStore::class));
		$auditRepository = new AuditRepository(self::createMock(QueryCollection::class), $userRepository);

		$queryCollection = self::createMock(QueryCollection::class);
		$queryCollection->method("delete")
			->with("deleteByFilePath")
			->willReturn(1);
		$sut = new UploadRepository($queryCollection, $auditRepository);
		$numPurged = $sut->purgeOldFiles($baseTmpDir);
		self::assertSame(6, $numPurged);
	}
}
