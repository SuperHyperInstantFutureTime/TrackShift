<?php
namespace SHIFT\TrackShift\Royalty;

use InvalidArgumentException;

enum Currency {
	case AUD;
	case CAD;
	case EUR;
	case GBP;
	case USD;
	case MXN;
	case NZD;

	public static function fromCode(mixed $code):self {
		foreach(self::cases() as $case) {
			if(strtoupper($case->name) === strtoupper($code)) {
				return $case;
			}
		}

		throw new InvalidArgumentException("Invalid currency code: $code");
	}

	public static function getSymbol(self $currency):string {
		return match($currency) {
			self::EUR => "€",
			self::GBP => "£",
			default => "$",
		};
	}

}
