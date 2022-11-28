<?php
namespace Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class UploadTestCase extends TestCase {
	public static function tearDownAfterClass():void {
		$tmpDir = self::getTmpTestDir();
		self::recursiveRemove($tmpDir);
	}

	/** @return string absolute file path of the temp file */
	protected static function getTempFile(string $testFileName, ?string $dirName = null):string {
		$testFilePath = realpath(__DIR__ . "/../../files/$testFileName");
		if(!is_file($testFilePath)) {
			throw new RuntimeException("Test file path not found: $testFileName");
		}
		$contents = file_get_contents($testFilePath);

		if(!$dirName) {
			$dirName = uniqid("trackshift-user-");
		}

		$filePath = self::getTmpTestDir() . $dirName . "/" . uniqid("trackshift-test-");
		if(!is_dir(dirname($filePath))) {
			mkdir(dirname($filePath), 0775, true);
		}
		file_put_contents($filePath, $contents);
		return $filePath;
	}

	private static function getTmpTestDir():string {
		return sys_get_temp_dir() . "/trackshift-tests/";
	}

	private static function recursiveRemove(string $path):void {
		if(is_dir($path)) {
			foreach(glob("$path/*") as $file) {
				self::recursiveRemove($file);
			}
			rmdir($path);
		}
		else {
			unlink($path);
		}
	}
}
