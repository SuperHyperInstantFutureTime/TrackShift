<?php
namespace Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class UploadTestCase extends TestCase {
	/** @return string absolute file path of the temp file */
	protected static function getTempFile(string $testFileName):string {
		$testFilePath = realpath("../files/$testFileName");
		if(!is_file($testFilePath)) {
			throw new RuntimeException("Test file path not found: $testFileName");
		}
		$contents = file_get_contents($testFilePath);

		$filePath = sys_get_temp_dir() . "/" . uniqid("trackshift-test-");
		file_put_contents($filePath, $contents);
		return $filePath;
	}
}
