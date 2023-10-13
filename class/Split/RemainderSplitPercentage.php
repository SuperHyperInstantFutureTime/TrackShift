<?php
namespace SHIFT\Trackshift\Split;

use SHIFT\Trackshift\Repository\Entity;
use SHIFT\Trackshift\Split\EmptySplitPercentage;

readonly class RemainderSplitPercentage extends Entity {
	public float $percentage;

	/** @param array<SplitPercentage> $percentageList */
	public function __construct(
		private array $percentageList,
		public string $owner = "You",
		public bool $isReadOnly = true,
	) {
		$remainder = 100;
		foreach($this->percentageList as $splitPercentage) {
			$remainder -= $splitPercentage->percentage;
		}

		$this->percentage = $remainder;
	}
}
