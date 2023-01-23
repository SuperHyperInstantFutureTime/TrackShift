<?php
namespace Trackshift\Usage;

use Countable;
use DateTime;
use DateTimeInterface;
use Gt\DomTemplate\BindGetter;
use Iterator;
use Trackshift\Upload\Upload;

/**
 * A Statement represents a collection of Uploads, whereas an Upload represents
 * a collection of Usages.
 * @implements Iterator<Upload>
 */
class Statement implements Iterator, Countable {
	const EXPIRY_SECONDS = 60 * 60 * 24 * 7 * 3; // 3 weeks

	/** @var array<Upload> */
	private array $uploadArray;
	private int $iteratorKey;

	public function __construct() {
		$this->uploadArray = [];
		$this->rewind();
	}

	public function addUpload(Upload $upload):void {
		array_push($this->uploadArray, $upload);
	}

	public function rewind():void {
		$this->iteratorKey = 0;
	}

	public function valid():bool {
		return isset($this->uploadArray[$this->key()]);
	}

	public function key():int {
		return $this->iteratorKey;
	}

	public function current():Upload {
		return $this->uploadArray[$this->key()];
	}

	public function next():void {
		$this->iteratorKey++;
	}

	public function count():int {
		return count($this->uploadArray);
	}

	public function clear():void {
		foreach($this as $upload) {
			$upload->delete();
		}

		$this->uploadArray = [];
	}

	public function getAggregatedUsages(string $propertyName):Aggregation {
		$aggregation = new Aggregation();

		foreach($this as $upload) {
			$uploadAggregation = $upload->getAggregatedUsages($propertyName);
			$aggregation = $aggregation->withAggregatedUsages($propertyName, $uploadAggregation);
		}

		return $aggregation;
	}

	public function getArtistUsages(string $propertyName):ArtistUsage {
		$artistAggregation = new ArtistUsage();

		foreach($this as $upload) {
			$uploadAggregation = $upload->getAggregatedUsages($propertyName);
			foreach($uploadAggregation as $usageList) {
				foreach($usageList as $usage) {
					$artistAggregation->addArtistUsage($usage->artist, $usage);
				}
			}
		}

		return $artistAggregation;
	}

	public function getExpiryDate():?DateTimeInterface {
		$upload = $this->uploadArray[0] ?? null;
		if(!$upload) {
			return null;
		}

		$uploadDir = dirname($upload->filePath);
		$createdAt = filemtime($uploadDir);
		$expiresAtDateTime = new DateTime();
		$expiresAtDateTime->setTimestamp($createdAt + self::EXPIRY_SECONDS);
		return $expiresAtDateTime;
	}

	public function isMultipleArtist():bool {
		foreach($this->uploadArray as $upload) {
			if($upload->isMultipleArtist()) {
				return true;
			}
		}

		return false;
	}
}
