<?php
namespace SHIFT\Trackshift\Split;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Repository\Entity;
use Traversable;

readonly class Split extends Entity implements IteratorAggregate {
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
