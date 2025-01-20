<?php
namespace SHIFT\TrackShift\Repository;

use Gt\Database\Database;

readonly class DatabaseTransaction {
	public function __construct(
		private Database $database,
	) {}

	public function start():void {
		$this->database->executeSql("start transaction");
	}

	public function commit():void {
		$this->database->executeSql("commit");
	}
}
