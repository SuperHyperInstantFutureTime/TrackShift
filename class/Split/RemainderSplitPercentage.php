<?php
namespace SHIFT\TrackShift\Split;

use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Split\EmptySplitPercentage;

readonly class RemainderSplitPercentage extends Entity {
	public float $percentage;

	/** @param array<SplitPercentage|EmptySplitPercentage> $percentageList */
	public function __construct(
		private array $percentageList,
		public string $owner = "You",
		public bool $isReadOnly = true,
	) {
		$remainder = 100;
		foreach($this->percentageList as $splitPercentage) {

			if($splitPercentage instanceof EmptySplitPercentage) {
				continue;
			}

			$remainder -= $splitPercentage->percentage;
		}

		$this->percentage = $remainder;
	}
}
