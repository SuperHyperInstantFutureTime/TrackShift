<?php
namespace Trackshift\Usage;

use Iterator;
use Trackshift\Royalty\Money;

/** @implements Iterator<string, UsageList> */
class Aggregation implements Iterator {
	/** @var array<string, array<Usage>> */
	private array $usageMap;
	private int $iteratorIndex;

	public function __construct() {
		$this->usageMap = [];
		$this->rewind();
	}

	public function add(string $key, Usage $usage):void {
		if(!isset($this->usageMap[$key])) {
			$this->usageMap[$key] = [];
		}

		array_push($this->usageMap[$key], $usage);
	}

	public function getTotalValue():Money {
		$total = new Money();

		foreach($this->usageMap as $key => $usageList) {
			$total = $total->withAddition(
				$this->getTotalValueForAggregate($key)
			);
		}

		return $total;
	}

	public function getTotalValueForAggregate(string $key):Money {
		$total = new Money();

		foreach($this->usageMap[$key] as $usage) {
			$total = $total->withAddition($usage->amount);
		}

		return $total;
	}

	public function rewind():void {
		$this->iteratorIndex = 0;
	}

	public function valid():bool {
		$mapKey = $this->getIteratorKeyString();
		return !is_null($mapKey);
	}

	public function current():UsageList {
		$mapKey = $this->getIteratorKeyString();
		$usageList = new UsageList();
		foreach($this->usageMap[$mapKey] as $usage) {
			$usageList->add($usage);
		}
		return $usageList;
	}

	public function key():string {
		return $this->getIteratorKeyString();
	}

	public function next():void {
		$this->iteratorIndex++;
	}

	private function getIteratorKeyString():string|null {
		$usageMapKeys = array_keys($this->usageMap);
		return $usageMapKeys[$this->iteratorIndex] ?? null;
	}

	public function withAggregatedUsages(
		string $propertyName,
		Aggregation $otherAggregation,
	):self {
		$clone = clone($this);

		foreach($otherAggregation as $usageList) {
			foreach($usageList as $usage) {
				$aggregateKey = $usage->{$propertyName} ?? null;
				$clone->add($aggregateKey, $usage);
			}
		}

		return $clone;
	}
}
