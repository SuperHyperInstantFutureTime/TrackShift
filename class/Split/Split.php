<?php
namespace SHIFT\TrackShift\Split;

use ArrayIterator;
use IteratorAggregate;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Repository\Entity;
use Traversable;

/** @implements IteratorAggregate<SplitPercentage> */
readonly class Split extends Entity implements IteratorAggregate {
	/** @param array<SplitPercentage> $splitPercentageList */
	public function __construct(
		public string $id,
		public User $user,
		public Product $product,
		public array $splitPercentageList = [],
	) {
	}

	public function getIterator():Traversable {
		$splitPercentageList = $this->splitPercentageList;
		array_push($splitPercentageList, new RemainderSplitPercentage($splitPercentageList));
		return new ArrayIterator($splitPercentageList);
	}
}
