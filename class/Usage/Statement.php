<?php
namespace Trackshift\Usage;

use Countable;
use Iterator;
use Trackshift\Upload\Upload;

/** @implements Iterator<Upload> */
class Statement implements Iterator, Countable {
	/** @var array<Upload> */
	private array $uploadArray;
	private int $iteratorKey;

	public function __construct() {
		$this->uploadArray = [];
		$this->rewind();
	}

	public function addUpload(Upload $upload):void {
		array_push($this->uploadArray, $upload);
	}

	public function rewind():void {
		$this->iteratorKey = 0;
	}

	public function valid():bool {
		return isset($this->uploadArray[$this->key()]);
	}

	public function key():int {
		return $this->iteratorKey;
	}

	public function current():Upload {
		return $this->uploadArray[$this->key()];
	}

	public function next():void {
		$this->iteratorKey++;
	}

	public function count():int {
		return count($this->uploadArray);
	}

	public function clear():void {
		foreach($this as $upload) {
			$upload->delete();
		}

		$this->uploadArray = [];
	}

	public function getAggregatedUsages(string $propertyName):Aggregation {
		$aggregation = new Aggregation();

		foreach($this as $upload) {
			$uploadAggregation = $upload->getAggregatedUsages($propertyName);
			$aggregation = $aggregation->withAggregatedUsages($propertyName, $uploadAggregation);
		}

		return $aggregation;
	}
}
