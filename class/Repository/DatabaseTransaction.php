<?php
namespace SHIFT\TrackShift\Repository;

class DatabaseTransaction {
	public function __construct(
		/** @var callable */
		private $startTransactionCallback,
		/** @var callable */
		private $commitTransactionCallback,
	) {}

	public function start():void {
		call_user_func($this->startTransactionCallback);
	}

	public function commit():void {
		call_user_func($this->commitTransactionCallback);
	}
}
