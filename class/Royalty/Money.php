<?php
namespace Trackshift\Royalty;

use Stringable;

class Money implements Stringable {
	const DECIMAL_ACCURACY = 10;

	public function __construct(
		public readonly float $value = 0,
	) {}

	public function __toString():string {
		$rounded = (float)substr($this->value, 0, 4);
		return "£" . number_format($rounded, 2);
	}

	public function withAddition(Money $add):self {
		$newValue = round($add->value, self::DECIMAL_ACCURACY) + round($this->value, self::DECIMAL_ACCURACY);
		$newValue = (float)substr($newValue, 0, 2 + self::DECIMAL_ACCURACY);
		return new Money($newValue);
	}
}
