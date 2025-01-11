<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindGetter;
use Gt\Logger\Log;
use League\Csv\Reader;
use League\Csv\ResultSet;
use League\Csv\Statement;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Content\NullByteFilter;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\Money;

abstract class Upload {
	const CURRENCY_COLUMN = null;
	const CURRENCY_OVERRIDE = null;
	const REQUIRES_PRELOADING = false;

	/** @var array<string, string> key = ISRC; value = UPC */
	public array $isrcUpcMap = [];
	/** @var array<string, string> key = UPC; value = Product title */
	public array $upcProductTitleMap = [];

	protected Reader $csvReader;
	protected int $rowIndex = 0;
	public readonly string $filename;
	public readonly string $basename;
	public readonly int $size;
	public readonly string $sizeString;
	public readonly string $type;
	public readonly DateTime $createdAt;
	public ?float $processedPercentage = null;

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

//		if (!in_array("strip_null_bytes", stream_get_filters())) {
//			stream_filter_register("strip_null_bytes", NullByteFilter::class);
//		}
		$fh = fopen($this->filePath, "r");
		$sample = fread($fh, 1024);
		fclose($fh);
		$encoding = mb_detect_encoding($sample, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'], true);

		$encodingMap = [
			'UTF-8' => null, // No conversion needed if already UTF-8
			'UTF-16LE' => 'convert.iconv.UTF-16LE.UTF-8',
			'UTF-16BE' => 'convert.iconv.UTF-16BE.UTF-8',
			'ISO-8859-1' => 'convert.iconv.ISO-8859-1.UTF-8',
			'Windows-1252' => 'convert.iconv.Windows-1252.UTF-8',
		];

		$this->openFile();

		if($streamName = $encodingMap[$encoding] ?? null) {
			$this->csvReader->appendStreamFilterOnRead($streamName);
		}
	}

	public function setProcessedPercentage(float $percentage):void {
		$this->processedPercentage = $percentage;
	}

	#[Bind("isProcessing")]
	public function isProcessing():bool {
		return $this->processedPercentage < 100;
	}

	#[BindGetter]
	public function getProcessedPercentageRounded():int {
		return $this->processedPercentage;
	}

	/** @param array<string, string> $row */
	abstract public function extractArtistName(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractProductTitle(array $row):string;

	/** @param array<string, string> $row */
	abstract public function extractEarning(array $row):Money;

	/** @param array<string, string> $row */
	abstract public function extractEarningDate(array $row):DateTime;

	public function preloadMissingProductTitleData(array $row):void {
		// TODO: Most uploads will not have anything to do here, but
		// for those that do, this will always be called.
	}

	public function getDefaultCurrency():Currency {
		$currency = null;

		foreach($this->csvReader as $rowData) {
			if($currencyCode = $rowData[static::CURRENCY_COLUMN]) {
				$currency = Currency::fromCode($currencyCode);
				break;
			}
		}

		return $currency ?? Currency::fromCode(static::CURRENCY_OVERRIDE);
	}

	/**
	 * @param array<string, string> $row
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function loadUsageForInternalLookup(array $row):void {
	}

	public function openFile():void {
		$this->csvReader = Reader::createFromPath($this->filePath);
		$this->csvReader->setHeaderOffset(0);
	}

	/**
	 * This function is the default behaviour for all Upload types - it Generates a set of key-value-pairs for each
	 * row in the file - the default behaviour is working with CSV data, but other types might use other formats.
	 * @return Generator<ResultSet>
	 */
	public function generateDataRows():Generator {
		$i = 0;

		do {
			$stmt = Statement::create()->offset($i)->limit(1);
			$resultSet = $stmt->process($this->csvReader);
			yield $resultSet;
			$i++;
		}
		while(count($resultSet) > 0);
	}

	/** @return array<string> */
	protected function getHeaderRow():array {
		return $this->csvReader->getHeader();
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
