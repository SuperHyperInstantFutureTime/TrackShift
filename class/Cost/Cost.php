<?php
namespace SHIFT\TrackShift\Cost;

use DateTime;
use Gt\DomTemplate\BindGetter;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Repository\Entity;
use SHIFT\TrackShift\Royalty\Money;

readonly class Cost extends Entity {
	public function __construct(
		public string $id,
		public Product $product,
		public string $description,
		public Money $amount,
	) {}

	#[BindGetter]
	public function getAddedOn():string {
		$ulid = new Ulid(init: $this->id);
		$timestamp = $ulid->getTimestamp() / 1000;
		$dateTime = new DateTime("@$timestamp");
		return $dateTime->format("jS M Y g:ia");
	}
}
