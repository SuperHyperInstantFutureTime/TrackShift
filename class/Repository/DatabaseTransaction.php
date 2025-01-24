<?php
namespace SHIFT\TrackShift\Repository;

use Gt\Database\Database;
use Gt\Logger\Log;

class DatabaseTransaction {
	private bool $fkChecksTurnedOff;

	public function __construct(
		private readonly Database $database,
	) {
		$this->fkChecksTurnedOff = false;
	}

	public function start(?string $message = null, bool $fkChecksOff = false):void {
		$fullMessage = "Starting transaction";
		if($fkChecksOff) {
			$fullMessage .= " without relations";
			$this->database->executeSql("set foreign_key_checks=0");
			$this->fkChecksTurnedOff = true;
		}

		if($message) {
			$fullMessage .= " - $message";
		}

		Log::debug($fullMessage);
		$this->database->executeSql("start transaction");
	}

	public function startWithoutRelations(?string $message = null):void {
		$this->start($message, true);
	}

	public function commit():void {
		Log::debug("Committing transaction");
		$this->database->executeSql("commit");

		if($this->fkChecksTurnedOff) {
			$this->database->executeSql("set foreign_key_checks=1");
		}
	}

	public function rollback(?string $message = null):void {
		$fullMessage = "Rolling back transaction";
		if($message) {
			$fullMessage .= " - $message";
		}

		Log::debug($fullMessage);
		$this->database->executeSql("rollback");
	}

}
