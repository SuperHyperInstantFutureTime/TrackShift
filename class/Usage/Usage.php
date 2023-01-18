<?php
namespace Trackshift\Usage;

use Trackshift\Artist\Artist;
use Trackshift\Royalty\Money;

class Usage {
	public function __construct(
		public readonly string $workTitle,
		public readonly Money $amount,
		public readonly ?Artist $artist = null,
	) {}
}
