<?php
namespace Trackshift\Upload;

use SplFileObject;
use Trackshift\Royalty\Money;
use Trackshift\Usage\Usage;
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

	abstract protected function processUsages():void;
}
