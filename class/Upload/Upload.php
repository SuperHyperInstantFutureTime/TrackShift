<?php
namespace Trackshift\Upload;

use SplFileObject;
use Trackshift\Artist\Artist;
use Trackshift\Artist\ArtistIdentifier;
use Trackshift\Royalty\Money;
use Trackshift\Usage\Aggregation;
use Trackshift\Usage\UsageList;

abstract class Upload {
	public readonly string $filename;
	public readonly int $size;
	public readonly string $sizeString;
	public readonly string $type;

	protected SplFileObject $file;
	protected UsageList $usageList;
	/** @var array<Artist> */
	protected array $artistList;
	protected ArtistIdentifier $artistIdentifier;

	public function __construct(
		public readonly string $filePath,
		?ArtistIdentifier $identifier = null,
	) {
		$this->file = new SplFileObject($this->filePath);
		$this->usageList = new UsageList();
		$this->artistList = [];
		if(!$identifier) {
			$this->artistIdentifier = new ArtistIdentifier();
		}

		$this->processUsages();

		$this->filename = pathinfo($this->filePath, PATHINFO_FILENAME);
		$this->size = filesize($this->filePath);

		$bytes = $this->size;
		$units = ["B", "KB", "MB", "GB", "TB", "PB"];
		for($i = 0; $bytes > 1024; $i++) {
			$bytes /= 1024;
		}
		$this->sizeString = round($bytes, 1)
			. " "
			. $units[$i];

		$className = get_class($this);
		$this->type = match($className) {
			default => str_replace("Upload", "", substr($className, strrpos($className, "\\") + 1)),
			PRSStatementUpload::class => "PRS Statement",
		};
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
			$aggregateKey = $usage->{$propertyName} ?? null;
// TODO: Throw meaningful exception here where aggregate key is null.
// It means the name has been picked incorrectly by the developer.
			$aggregation->add($aggregateKey, $usage);
		}

		return $aggregation;
	}

	abstract protected function processUsages():void;

	public function delete():void {
		$path = $this->file->getRealPath();
		unset($this->file);
		unlink($path);
		$this->usageList = new UsageList();
	}

	public function getArtist(string $identifier):Artist {
		return $this->artistIdentifier->identify(
			$identifier,
			self::class,
		);
	}

	public function isMultipleArtist():bool {
		return count($this->artistList) > 1;
	}

	/**
	 * TODO: Extract this into a CSVProcessor trait or similar.
	 * Convert an indexed array of row data into an associative array,
	 * according to the provided header row.
	 * @param array<string> $headerRow
	 * @param array<string> $row
	 * @return array<string, string>
	 */
	protected function rowToData(array $headerRow, array $row):array {
		$data = [];
		foreach($row as $i => $datum) {
			$data[$headerRow[$i]] = $datum;
		}
		return $data;
	}
}
