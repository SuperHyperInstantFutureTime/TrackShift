<?php
namespace SHIFT\TrackShift\Split;

use SHIFT\TrackShift\Repository\Entity;

readonly class SplitPercentage extends Entity {
	public function __construct(
		public string $id,
		public string $owner,
		public float $percentage,
		public string $contact,
		public bool $isReadOnly = true,
	) {}
}
