<?php
namespace SHIFT\Trackshift\Upload;

use DateInterval;
use DateTime;
use DateTimeZone;
use Gt\Input\InputData\Datum\FileUpload;
use SHIFT\Trackshift\Auth\User;
use SplFileObject;
use SHIFT\Trackshift\Usage\Statement;

class UploadManager {
	public function upload(User $user, FileUpload...$uploadList):void {
		$userDir = $this->getUserDataDir($user);
		foreach($uploadList as $file) {
			$originalFileName = $file->getClientFilename();

			$targetPath = "$userDir/$originalFileName";
			if(!is_dir(dirname($targetPath))) {
				mkdir(dirname($targetPath), 0775, true);
			}
			$file->moveTo($targetPath);
		}
	}

	/** @return array<Upload> */
	public function getUploadsForUser(User $user):array {
		$userDir = $this->getUserDataDir($user);
		$uploadList = [];

		foreach(glob("$userDir/*") as $filePath) {
			$uploadType = $this->detectUploadType($filePath);
			array_push(
				$uploadList,
				new $uploadType($filePath)
			);
		}

		return $uploadList;
	}

	public function delete(User $user, ?string $fileName):void {
		$userDir = $this->getUserDataDir($user);
		$filePath = "$userDir/$fileName";
		if(!is_file($filePath)) {
			throw new UploadNotFoundException($filePath);
		}

		unlink($filePath);
	}

	public function extendExpiry(User $user):void {
		$userDir = $this->getUserDataDir($user);
		touch($userDir);
	}

	public function clearUserFiles(User $user):void {
		$userDir = $this->getUserDataDir($user);
		$this->recursiveRemove($userDir);
	}

	public function getExpiry(User $user):DateTime {
		$userDir = $this->getUserDataDir($user);
		$expiry = new DateTime("@" . filemtime($userDir));
		$expiry->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$expiry->add(new DateInterval("P3W"));
		return $expiry;
	}

	public function load(string...$filePathList):Statement {
		return $this->loadInto(new Statement(), ...$filePathList);
	}

	public function loadInto(Statement $statement, string...$filePathList):Statement {
		foreach($filePathList as $filePath) {
			$upload = null;
			if($this->isCsv($filePath)) {
				if($this->hasCsvColumns($filePath, "Record Number", "CAE Number", "Work Title", "Amount (performance revenue)")) {
					$upload = new PRSStatementUpload($filePath);
				}
				elseif($this->hasCsvColumns($filePath, "item type", "item name", "artist", "bandcamp transaction id")) {
					$upload = new BandcampUpload($filePath);
				}
			}

			if(is_null($upload)) {
				$upload = new UnknownUpload($filePath);
			}

			$statement->addUpload($upload);
		}

		return $statement;
	}

	public function purge(string $dir = "data"):int {
		$count = 0;
		$expiredTimestamp = strtotime("-3 weeks");

		foreach(glob("$dir/*") as $file) {
			if(is_dir($file)) {
				$file .= "/.";
			}

			if(filemtime($file) <= $expiredTimestamp) {
				$count += $this->recursiveRemove($file);
				rmdir(rtrim($file, "."));
			}
		}
		return $count;
	}

	private function recursiveRemove(string $filePath):int {
		$count = 0;
		if(is_dir($filePath)) {
			foreach(glob("$filePath/*") as $subFile) {
				$count += $this->recursiveRemove($subFile);
			}
		}
		else {
			unlink($filePath);
			$count++;
		}

		return $count;
	}

	private function getUserDataDir(User $user):string {
		return "data/$user->id";
	}

	/** @return class-string */
	private function detectUploadType(mixed $filePath):string {
		$type = UnknownUpload::class;

		if($this->isCsv($filePath)) {
			if($this->hasCsvColumns($filePath, ...PRSStatementUpload::KNOWN_CSV_COLUMNS)) {
				$type = PRSStatementUpload::class;
			}
			elseif($this->hasCsvColumns($filePath, ...BandcampUpload::KNOWN_CSV_COLUMNS)) {
				$type = BandcampUpload::class;
			}
		}

		return $type;
	}

	private function isCsv(string $filePath):bool {
		$file = new SplFileObject($filePath);
		$firstLine = $file->fgetcsv();
		return (bool)$firstLine;
	}

	private function hasCsvColumns(
		string $filePath,
		string...$columnsToCheck
	):bool {
		$file = new SplFileObject($filePath);
		$firstLine = $file->fgetcsv();
		foreach($firstLine as $i => $column) {
			$firstLine[$i] = preg_replace(
				'/[[:^print:]]/',
				'',
				$column
			);
		}
		$foundAllColumns = true;

		foreach($columnsToCheck as $columnName) {
			if(!in_array($columnName, $firstLine)) {
				$foundAllColumns = false;
			}
		}

		return $foundAllColumns;
	}

}
