<?php
namespace Trackshift\Test\Upload;

use PHPUnit\Framework\TestCase;

abstract class UploadTestCase extends TestCase {
	/** @return string absolute file path of the temp file */
	protected static function getTempFile($content):string {
		$filePath = sys_get_temp_dir() . "/" . uniqid("trackshift-test-");
		file_put_contents($filePath, $content);
		return $filePath;
	}
}
