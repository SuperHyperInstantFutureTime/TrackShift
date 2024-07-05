<?php
namespace SHIFT\TrackShift\Royalty;

use InvalidArgumentException;

enum Currency:string {
	case EUR = "€";
	case GBP = "£";
	case USD = "$";

	public static function fromCode(mixed $code):self {
		foreach(self::cases() as $case) {
			if(strtoupper($case->name) === strtoupper($code)) {
				return $case;
			}
		}

		throw new InvalidArgumentException("Invalid currency code: $code");
	}

}
