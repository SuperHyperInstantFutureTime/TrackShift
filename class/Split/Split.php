<?php
namespace SHIFT\Trackshift\Split;

use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Repository\Entity;

readonly class Split extends Entity {
	public function __construct(
		public string $id,
		public User $user,
		public Product $product,
	) {}
}
