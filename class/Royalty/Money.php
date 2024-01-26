<?php
namespace SHIFT\TrackShift\Royalty;

use Stringable;

class Money implements Stringable {
	const DECIMAL_ACCURACY = 10;

	public function __construct(
		public readonly float $value = 0,
	) {}

	public function __toString():string {
		if($this->value === 0.0) {
			return "-";
		}

		if($this->value <= 0.02) {
			return "< £0.01";
		}

		return "£" . number_format($this->value, 2);
	}

	public function withAddition(Money $add):self {
		$newValue = round($add->value, self::DECIMAL_ACCURACY) + round($this->value, self::DECIMAL_ACCURACY);
		$newValue = (float)substr((string)$newValue, 0, 2 + self::DECIMAL_ACCURACY);
		return new Money($newValue);
	}

	public function withSubtraction(Money $sub):self {
		$newValue = round($this->value, self::DECIMAL_ACCURACY) - round($sub->value, self::DECIMAL_ACCURACY);
		$newValue = (float)substr((string)$newValue, 0, 2 + self::DECIMAL_ACCURACY);
		return new Money($newValue);
	}
}
