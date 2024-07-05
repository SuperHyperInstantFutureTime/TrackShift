<?php
namespace SHIFT\TrackShift\Royalty;

use DateTime;

class Earning extends Money {
	public function __construct(
		public readonly DateTime $earningDate,
		float $value = 0.0,
		?Currency $currency = null,
	) {
		parent::__construct($value, $currency);
	}
}
