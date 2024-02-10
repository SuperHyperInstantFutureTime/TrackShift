<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Royalty\Money;

abstract class Upload {
	/** @var array<string, string> key = UPC; value = Product title */
	public array $upcProductTitleMap = [];
	/** @var array<string, string> key = ISRC; value = UPC */
	public array $isrcUpcMap = [];

	/** @var resource */
	protected $fileHandle;
	public readonly string $filename;
	public readonly string $basename;
	public readonly int $size;
	public readonly string $sizeString;
	public readonly string $type;
	public readonly DateTime $createdAt;
	protected string $dataRowCsvSeparator = ",";

	public function __construct(
		public readonly string $id,
		public string $filePath,
		public readonly Money $totalEarnings = new Money(0),
		public ?DateTimeInterface $usagesProcessed = null,
	) {
		if(!is_file($this->filePath)) {
			throw new UploadFileNotFoundException($this->filePath);
		}
		$this->filename = pathinfo($this->filePath, PATHINFO_FILENAME);
		$this->basename = pathinfo($this->filePath, PATHINFO_BASENAME);
		$this->size = filesize($this->filePath);
// TODO: Use the ULID to get the timestamp from the ID.
		$this->createdAt = new DateTime("@" . filectime($this->filePath));
		$this->createdAt->setTimezone(new DateTimeZone(date_default_timezone_get()));

		$this->sizeString = $this->calculateSizeString();

		$className = get_class($this);
		$this->type = match($className) {
			default => str_replace("Upload", "", substr($className, strrpos($className, "\\") + 1)),
			PRSStatementUpload::class => "PRS",
			BandcampUpload::class => "Bandcamp",
			CargoDigitalUpload::class => "Cargo Digital",
			CargoPhysicalUpload::class => "Cargo Physical",
			TuneCoreUpload::class => "TuneCore",
			DistroKidUpload::class => "DistroKid",
			CdBabyUpload::class => "CD Baby",
		};

		$this->fileHandle = $this->openFile();
	}

	#[Bind("isProcessing")]
	public function isProcessing():bool {
		return is_null($this->usagesProcessed);
	}

	/** @param array<string, string> $row */
	abstract public function extractArtistName(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractProductTitle(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractEarning(array $row):Money;

	/**
	 * @param array<string, string> $row
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function loadUsageForInternalLookup(array $row):void {
	}

	/** @return resource */
	public function openFile() {
		return fopen($this->filePath, "r");
	}

	/**
	 * This function is the default behaviour for all Upload types - it Generates a set of key-value-pairs for each
	 * row in the file - the default behaviour is working with CSV data, but other types might use other formats.
	 * @return Generator<array<string, string>>
	 */
	public function generateDataRows():Generator {
		$headerRow = null;

		while(!feof($this->fileHandle)) {
			$line = fgets($this->fileHandle);
			$line = $this->correctEncoding($line);
			$line = $this->stripNullBytes($line);
			$row = str_getcsv($line, $this->dataRowCsvSeparator);

			if(!$row[0]) {
				continue;
			}
			if(!$headerRow) {
				$headerRow = $row;
				continue;
			}

			yield $this->rowToData($headerRow, $row);
		}
		fseek($this->fileHandle, 0);
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

	private function correctEncoding(string $line):string {
		$encoding = mb_detect_encoding($line, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'], true);
		if($encoding !== "UTF-8") {
			if (substr($line, 0, 2) === "\xFF\xFE") {
				$line = substr($line, 2);
			}
			$line = mb_convert_encoding($line, "UTF-8", $encoding);
		}
		return $line;
	}


	protected function stripNullBytes(string $line):string {
		return $line;
	}

	protected function calculateSizeString():string {
		$bytes = $this->size;
		$units = ["B", "KB", "MB", "GB", "TB", "PB"];
		for($i = 0; $bytes > 1024; $i++) {
			$bytes /= 1024;
		}
		return round($bytes, 1)
			. " "
			. $units[$i];
	}
}
