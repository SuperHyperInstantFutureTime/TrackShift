<?php
namespace SHIFT\TrackShift\Content;

class FileFixer {
	public function __construct() {}

	public function fix(string $filePath):void {
		$contents = file_get_contents($filePath);

// Detect and remove BOM (UTF-8 and UTF-16)
		$bomMarkers = [
			"\xEF\xBB\xBF", // UTF-8 BOM
			"\xFF\xFE",     // UTF-16 LE BOM
			"\xFE\xFF",     // UTF-16 BE BOM
		];

		foreach ($bomMarkers as $bom) {
			if (str_starts_with($contents, $bom)) {
				$contents = substr($contents, strlen($bom));
				break;
			}
		}

		if($encoding = mb_detect_encoding($contents, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'ASCII'], true)) {
			if($encoding !== 'UTF-8') {
				$contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
			}
		}

		$contents = str_replace(["\r\n", "\r"], "\n", $contents);
		$contents = str_replace("\0", "", $contents);

		$contents = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $contents);

		// Remove other control characters, but preserve \n and \r
		$contents = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $contents);

		file_put_contents($filePath, $contents);
	}
}
