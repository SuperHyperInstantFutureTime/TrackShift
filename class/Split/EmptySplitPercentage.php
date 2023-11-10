<?php
namespace SHIFT\Trackshift\Split;

use Gt\DomTemplate\Bind;
use SHIFT\Trackshift\Repository\Entity;

readonly class EmptySplitPercentage extends Entity {
	public function __construct(
		public string $productId,
	) {}
}
