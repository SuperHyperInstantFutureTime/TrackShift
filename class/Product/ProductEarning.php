<?php
namespace SHIFT\TrackShift\Product;

use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Royalty\Money;

readonly class ProductEarning extends Entity {
	public function __construct(
		public User $user,
		public Product $product,
		public Money $earning,
		public Money $cost,
		private Money $outgoing,
		public Money $profit,
	) {}

	public function getArtistName():string {
		return $this->product->artist->name;
	}

	public function getProductTitle():string {
		return $this->product->getTitle();
	}

	#[BindGetter]
	public function getBalance():?string {
		if($this->cost->value) {
			return $this->earning->withSubtraction($this->cost);
		}

		return null;
	}

	#[BindGetter]
	public function getOutgoing():?string {
		if($this->outgoing->value) {
			return $this->outgoing;
		}

		return null;
	}
}
