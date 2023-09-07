<?php
namespace SHIFT\Trackshift\Usage;

use SHIFT\Trackshift\Repository\Entity;
use SHIFT\Trackshift\Upload\Upload;

readonly class Usage extends Entity {
	/** @param array<string, string> $row */
	public function __construct(
		public string $id,
		public Upload $upload,
		public array $row,
	) {}
}
