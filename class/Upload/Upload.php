<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindGetter;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;

abstract class Upload {
	const CURRENCY_COLUMN = null;
	const CURRENCY_OVERRIDE = null;

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
	/** @var array<string> */
	protected array $headerRow;

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

	/** @param array<string, string> $row */
	abstract public function extractEarningDate(array $row):DateTime;

	public function getDefaultCurrency():Currency {
		$cursor = ftell($this->fileHandle);

		$rowData = $this->getNextRowData();
		$currency = is_null(static::CURRENCY_OVERRIDE)
			? Currency::fromCode($rowData[static::CURRENCY_COLUMN])
			: Currency::fromCode(static::CURRENCY_OVERRIDE);

		fseek($this->fileHandle, $cursor);

		return $currency;
	}

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
	 * @return Generator<null|array<string, string>>
	 */
	public function generateDataRows():Generator {
		while(!feof($this->fileHandle)) {
			$nextRowData = $this->getNextRowData();
			if($nextRowData) {
				yield $nextRowData;
			}
		}
		fseek($this->fileHandle, 0);
	}

	/** @return array<string> */
	protected function getHeaderRow():array {
		$cursor = ftell($this->fileHandle);
		$line = fgets($this->fileHandle);
		$line = $this->stripNullBytes($line);
		$line = $this->correctEncoding($line);
		$row = str_getcsv($line, $this->dataRowCsvSeparator);

		if($cursor > 0) {
			fseek($this->fileHandle, $cursor);
		}

		return $row;
	}

	/** @return null|array<string, string> */
	protected function getNextRowData():?array {
		if(!isset($this->headerRow)) {
			$this->headerRow = $this->getHeaderRow();
		}

		if(ftell($this->fileHandle) === 0) {
			fgets($this->fileHandle);
		}

		$line = fgets($this->fileHandle);
		$line = $this->stripNullBytes($line);
		$line = $this->correctEncoding($line);
		$row = str_getcsv($line, $this->dataRowCsvSeparator);

		if(!$row[0]) {
			return null;
		}

		return $this->rowToData($this->headerRow, $row);
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
		$line = mb_convert_encoding($line, "UTF-8", "UTF-8");
		return str_replace(["\xEF", "\xBB", "\xBF"], "", $line);
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
