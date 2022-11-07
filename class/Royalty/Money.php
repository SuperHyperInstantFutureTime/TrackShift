<?php
namespace Trackshift\Royalty;

use Stringable;

class Money implements Stringable {
	public function __construct(
		public readonly float $value = 0,
	) {}

	public function __toString():string {
		$rounded = (float)substr($this->value, 0, 4);
		return "£" . number_format($rounded, 2);
	}

	public function withAddition(Money $add):self {
		return new Money(round($add->value, 10) + round($this->value, 10));
	}
}
