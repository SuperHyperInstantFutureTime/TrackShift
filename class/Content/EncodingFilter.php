<?php
namespace SHIFT\TrackShift\Content;

readonly class EncodingFilter {
	public ?string $encoding;

	public function __construct(string $filePath) {
		$fh = fopen($filePath, "r");
		$sample = fread($fh, 1024);
		fclose($fh);
		$this->encoding = mb_detect_encoding(
			$sample,
			['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'],
			true
		) ?: null;
	}

	public function getStreamName():?string {
		$encodingMap = [
			"UTF-8" => null, // No conversion needed if already UTF-8
			"UTF-16LE" => 'convert.iconv.UTF-16LE.UTF-8',
			"UTF-16BE" => 'convert.iconv.UTF-16BE.UTF-8',
			"ISO-8859-1" => 'convert.iconv.ISO-8859-1.UTF-8',
			"Windows-1252" => 'convert.iconv.Windows-1252.UTF-8',
		];

		foreach($encodingMap as $code => $name) {
			if(strcasecmp($this->encoding, $code) === 0) {
				return $name;
			}
		}

		return null;
	}

}
