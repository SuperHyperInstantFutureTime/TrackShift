<?php
namespace SHIFT\Trackshift\Upload;

use DateTime;
use DateTimeZone;
use Gt\DomTemplate\BindGetter;
use SplFileObject;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Artist\ArtistIdentifier;
use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Aggregation;
use SHIFT\Trackshift\Usage\UsageList;

abstract class Upload {
	public readonly string $filename;
	public readonly string $basename;
	public readonly int $size;
	public readonly string $sizeString;
	public readonly string $type;
	public readonly DateTime $uploadedAt;

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
		$this->basename = pathinfo($this->filePath, PATHINFO_BASENAME);
		$this->size = filesize($this->filePath);
		$this->uploadedAt = new DateTime("@" . filectime($this->filePath));
		$this->uploadedAt->setTimezone(new DateTimeZone(date_default_timezone_get()));

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

	#[BindGetter]
	public function getUploadedAtFormattedDate():string {
		return $this->uploadedAt->format("d/m/Y");
	}

	#[BindGetter]
	public function getUploadedAtFormattedTime():string {
		return $this->uploadedAt->format("H:i");
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
			if(isset($headerRow[$i])) {
				$data[$headerRow[$i]] = $datum;
			}
		}
		return $data;
	}

	/**
	 * @param string|array<?string> $data
	 * @return string|array<?string>
	 */
	protected function stripNullBytes(string|array $data):string|array {
		if(empty($data) || is_null($data[0])) {
			return $data;
		}
		$input = $data;
		if(!is_array($input)) {
			$input = [$input];
		}

		foreach($input as $i => $value) {
			$input[$i] = preg_replace(
				'/[[:^print:]]/',
				'',
				$value
			);
		}

		if(is_string($data)) {
			return $input[0];
		}
		return $input;
	}
}
