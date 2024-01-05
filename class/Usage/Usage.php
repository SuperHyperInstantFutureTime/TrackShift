<?php
namespace SHIFT\TrackShift\Usage;

use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Upload\Upload;

readonly class Usage extends Entity {
	/** @param array<string, string> $row */
	public function __construct(
		public string $id,
		public Upload $upload,
		public array $row,
	) {}
}
