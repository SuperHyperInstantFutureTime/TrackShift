<?php
namespace Trackshift\Upload;

use SplFileObject;
use Trackshift\Usage\Statement;

class UploadManager {
	public function load(string...$filePathList):Statement {
		$statement = new Statement();

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
}
