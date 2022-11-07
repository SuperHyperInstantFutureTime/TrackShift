<?php
namespace Trackshift\Upload;

use SplFileObject;
use Trackshift\Royalty\Money;
use Trackshift\Usage\Aggregation;
use Trackshift\Usage\UsageList;

abstract class Upload {
	protected SplFileObject $file;
	protected UsageList $usageList;

	public function __construct(
		public readonly string $filePath,
	) {
		$this->file = new SplFileObject($this->filePath);
		$this->processUsages();
	}

	public function getUsageTotal():Money {
		$total = new Money();
		foreach($this->usageList as $usage) {
			$total = $total->withAddition($usage->amount);
		}

		return $total;
	}

	public function getAggregatedUsages(string $propertyName):Aggregation {
		$aggregation = new Aggregation();

		foreach($this->usageList as $usage) {
			$aggregateKey = $usage->{$propertyName};
			$aggregation->add($aggregateKey, $usage);
		}

		return $aggregation;
	}

	abstract protected function processUsages():void;
}
