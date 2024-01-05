<?php
namespace SHIFT\TrackShift\Split;

use Gt\DomTemplate\Bind;
use SHIFT\TrackShift\Repository\Entity;

readonly class EmptySplitPercentage extends Entity {
	public function __construct(
		public string $productId,
	) {}
}
