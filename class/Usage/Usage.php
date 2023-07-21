<?php
namespace SHIFT\Trackshift\Usage;

use Gt\DomTemplate\BindGetter;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Royalty\Money;

class Usage {
	public function __construct(
		public readonly string $workTitle,
		public readonly Money $amount,
		public readonly ?Artist $artist = null,
	) {}

	#[BindGetter]
	public function getAmountFormatted():string {
		return $this->amount;
	}
}
