<?php
namespace Trackshift\Upload;

use DateTime;
use SplFileObject;
use Trackshift\Usage\Statement;

class UploadManager {
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
			}

			if(is_null($upload)) {
				$upload = new UnknownUpload($filePath);
			}

			$statement->addUpload($upload);
		}

		return $statement;
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
		$foundAllColumns = true;

		foreach($columnsToCheck as $columnName) {
			if(!in_array($columnName, $firstLine)) {
				$foundAllColumns = false;
			}
		}

		return $foundAllColumns;
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
}
