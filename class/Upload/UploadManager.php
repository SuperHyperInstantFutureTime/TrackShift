<?php
namespace Trackshift\Upload;

use SplFileObject;

class UploadManager {
	public function load(string $filePath):Upload {
		if($this->isCsv($filePath)) {
			if($this->hasCsvColumns($filePath, "Record Number", "CAE Number", "Work Title", "Amount (performance revenue)")) {
				return new PRSStatementUpload($filePath);
			}
		}

		return new UnknownUpload($filePath);
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
}
