<?php
namespace SHIFT\Trackshift\Egg;
use Iterator;

/** @implements Iterator<string> */
class UploadMessageList implements Iterator {
	private const AVAILABLE_MESSAGE_STRINGS = [
		"Checking Tracklist",
		"Reading Liner Notes",
		"Dropping the needle",
		"Plugging in Headphones",
		"Setting Volume",
		"Warming the tubes",
		"Reticulating splines",
		"Smelling the valve dust",
		"Set reproducer reference level: 1000 Hurts",
	];

	/** @var array<string> */
	private array $messages;

	public function __construct(int $numMessages) {
		$messageArray = self::AVAILABLE_MESSAGE_STRINGS;
		shuffle($messageArray);
		$this->messages = array_slice($messageArray, 0, $numMessages);
	}

	public function rewind():void {
		reset($this->messages);
	}

	public function valid():bool {
		return isset($this->messages[$this->key()]);
	}

	public function next():void {
		next($this->messages);
	}

	public function key():?int {
		return key($this->messages);
	}

	public function current():string {
		return $this->messages[$this->key()];
	}
}
