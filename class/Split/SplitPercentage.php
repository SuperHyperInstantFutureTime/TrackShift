<?php
namespace SHIFT\Trackshift\Split;

use SHIFT\Trackshift\Repository\Entity;

readonly class SplitPercentage extends Entity {
	public function __construct(
		public string $id,
		public Split $split,
		public string $owner,
		public float $percentage,
		public string $email,
	) {}
}
