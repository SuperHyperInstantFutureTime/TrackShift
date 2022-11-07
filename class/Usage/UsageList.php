<?php
namespace Trackshift\Usage;

use Iterator;

/**
 * @implements Iterator<Usage>
 */
class UsageList implements Iterator {
	/** @var array<Usage> */
	private array $internalArray;
	private int $iteratorIndex;

	public function __construct() {
		$this->internalArray = [];
		$this->rewind();
	}

	public function add(Usage $usage):void {
		array_push($this->internalArray, $usage);
	}

	public function rewind():void {
		$this->iteratorIndex = 0;
	}

	public function valid():bool {
		return isset($this->internalArray[$this->iteratorIndex]);
	}

	public function current():Usage {
		return $this->internalArray[$this->iteratorIndex];
	}

	public function key():int {
		return $this->iteratorIndex;
	}

	public function next():void {
		$this->iteratorIndex++;
	}
}
