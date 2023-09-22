<?php
namespace SHIFT\Trackshift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Entity;
use SHIFT\Trackshift\Royalty\Money;

readonly class ProductEarning extends Entity {
	public function __construct(
		public User $user,
		public Product $product,
		public Money $earning,
		public Money $cost,
	) {}

	public function getArtistName():string {
		return $this->product->artist->name;
	}

	public function getProductTitle():string {
		return $this->product->title;
	}

	#[BindGetter]
	public function getBalance():?string {
		if($this->cost->value) {
			return $this->earning->withSubtraction($this->cost);
		}

		return null;
	}
}
