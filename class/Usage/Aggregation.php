<?php
namespace Trackshift\Usage;

use Trackshift\Royalty\Money;

class Aggregation {
	/** @var array<string, array<Usage>> */
	private array $usageMap;

	public function __construct() {
		$this->usageMap = [];
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
}
