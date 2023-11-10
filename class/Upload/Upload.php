<?php
namespace SHIFT\Trackshift\Upload;

use DateTime;
use DateTimeZone;
use Generator;
use Gt\DomTemplate\BindGetter;
use SplFileObject;
use SHIFT\Trackshift\Royalty\Money;

abstract class Upload {
	protected SplFileObject $file;
	public readonly string $filename;
	public readonly string $basename;
	public readonly int $size;
	public readonly string $sizeString;
	public readonly string $type;
	public readonly DateTime $createdAt;

	public function __construct(
		public readonly string $id,
		public readonly string $filePath,
		public readonly Money $totalEarnings = new Money(0),
	) {
		$this->file = new SplFileObject($this->filePath);
		$this->filename = pathinfo($this->filePath, PATHINFO_FILENAME);
		$this->basename = pathinfo($this->filePath, PATHINFO_BASENAME);
		$this->size = filesize($this->filePath);
// TODO: Use the ULID to get the timestamp from the ID.
		$this->createdAt = new DateTime("@" . filectime($this->filePath));
		$this->createdAt->setTimezone(new DateTimeZone(date_default_timezone_get()));

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
			PRSStatementUpload::class => "PRS",
			BandcampUpload::class => "Bandcamp",
			CargoUpload::class => "Cargo",
			TunecoreUpload::class => "Tunecore",
		};
	}

	/** @param array<string, string> $row */
	abstract public function extractArtistName(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractProductTitle(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractEarning(array $row):Money;

	/** @return Generator<array<string, string>> */
	public function generateDataRows():Generator {
		$headerRow = null;

		$this->file->rewind();
		while(!$this->file->eof()) {
			$row = $this->stripNullBytes($this->file->fgetcsv());
			if(empty($row) || !$row[0]) {
				continue;
			}
			if(!$headerRow) {
				$headerRow = $row;
				continue;
			}

			yield $this->rowToData($headerRow, $row);
		}
	}

	#[BindGetter]
	public function getCreatedAtFormattedDate():string {
		return $this->createdAt->format("d/m/Y");
	}

	#[BindGetter]
	public function getUploadedAtFormattedTime():string {
		return $this->createdAt->format("H:i");
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
