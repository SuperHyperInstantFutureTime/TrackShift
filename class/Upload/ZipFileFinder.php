<?php
namespace SHIFT\TrackShift\Upload;

use Stringable;
use ZipArchive;

/**
 * If the file is a zip, we unzip to a temporary directory, then find the desired file within the directory.
 * If one is found, its absolute path will be returned, otherwise the original filePath will be used
 * with no change.
 */
class ZipFileFinder implements Stringable {
	const MATCHING_FILE_LIST = [
		CargoDigitalUpload::class => "*-royalty_extended.csv",
	];

	public function __construct(private readonly string $filePath) {}

	public function __toString():string {
		$tmpDir = sys_get_temp_dir() . "/trackshift/upload/$this->filePath/";
		if(is_dir($tmpDir)) {

		}
		else {
			mkdir($tmpDir, recursive: true);
		}

		$zip = new ZipArchive();
		$status = $zip->open($this->filePath);
		if(true !== $status) {
			throw new ZipNotFoundException("$this->filePath ({$zip->getStatusString()})");
		}
		$zip->extractTo($tmpDir);
		$zip->close();

		foreach(self::MATCHING_FILE_LIST as $pattern) {
			$files = glob("$tmpDir/$pattern");
			if(!empty($files)) {
				return $files[0];
			}
		}

		return $this->filePath;
	}
}
