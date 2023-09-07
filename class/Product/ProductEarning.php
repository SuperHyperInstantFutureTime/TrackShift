<?php
namespace SHIFT\Trackshift\Product;

use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Entity;
use SHIFT\Trackshift\Royalty\Money;

readonly class ProductEarning extends Entity {
	public function __construct(
		public User $user,
		public Product $product,
		public Money $earning,
	) {}

	public function getArtistName():string {
		return $this->product->artist->name;
	}

	public function getProductTitle():string {
		return $this->product->title;
	}
}
