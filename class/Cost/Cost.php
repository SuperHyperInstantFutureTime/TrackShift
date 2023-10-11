<?php
namespace SHIFT\Trackshift\Cost;

use DateTime;
use Gt\DomTemplate\BindGetter;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Repository\Entity;
use SHIFT\Trackshift\Royalty\Money;

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
