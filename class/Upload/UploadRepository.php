<?php
namespace SHIFT\TrackShift\Upload;
use Gt\Database\Result\Row;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Money;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
readonly class UploadRepository extends Repository {
	const DIR_UPLOAD = "data/upload";

	public function purgeOldFiles():void {
// TODO: this needs to be tested and implemented.
	}

	/** @return array<Upload> */
	public function create(User $user, FileUpload...$uploadList):array {
		$completedUploadList = [];
		$userDir = $this->getUserDataDir($user);

		foreach($uploadList as $uploadedFile) {
			$originalFileName = $uploadedFile->getClientFilename();
			$targetPath = "$userDir/$originalFileName";

			if($this->db->fetch("findByFilePath", $targetPath)) {
				continue;
			}

			if(!is_dir(dirname($targetPath))) {
				mkdir(dirname($targetPath), 0775, true);
			}
			$uploadedFile->moveTo($targetPath);

// TODO: Handle the file encoding.
			$this->ensureCorrectEncoding($targetPath);
			$this->ensureUnixLineEnding($targetPath);
			$this->ensureSeparatorMatchesExtension($targetPath);

			$uploadType = $this->detectUploadType($targetPath);
			/** @var Upload $upload */
			$upload = new $uploadType(new Ulid("upload"), $targetPath);

			$this->db->insert("create", [
				"id" => $upload->id,
				"userId" => $user->id,
				"filePath" => $upload->filePath,
				"type" => $upload::class,
			]);

//			if($upload instanceof UnknownUpload) {
//				$this->auditRepository->notify(
//					$user,
//					"Your latest upload was not processed ($upload->filename)",
//					$upload->id,
//				);
//			}
//			else {
//				$this->auditRepository->create(
//					$user,
//					$upload->id,
//					$upload->filename,
//				);
//			}

			array_push($completedUploadList, $upload);
		}

		return $completedUploadList;
	}

	private function getUserDataDir(User $user):string {
		return self::DIR_UPLOAD . "/$user->id";
	}

	public function getById(string $id, User $user):?Upload {
		return $this->rowToUpload($this->db->fetch("getById", [
			"id" => $id,
			"userId" => $user->id
		]));
	}

	/** @return array<Upload> */
	public function getUploadsForUser(User $user):array {
		$uploadList = [];

		foreach($this->db->fetchAll("getForUser", [
			"userId" => $user->id,
		]) as $row) {
			if($upload = $this->rowToUpload($row)) {
				array_push(
					$uploadList,
					$upload,
				);
			}
		}

		return $uploadList;
	}

	public function delete(Upload $upload, User $user):void {
		$this->db->update("invalidateProductCache", $upload->id);
		$this->db->delete("delete", [
			"id" => $upload->id,
			"userId" => $user->id,
		]);
		unlink($upload->filePath);
	}

	public function clearUserData(User $user):void {
		$this->db->delete("deleteAllForUser", $user->id);
		$userDir = $this->getUserDataDir($user);
		foreach(glob("$userDir/*") as $filePath) {
			unlink($filePath);
		}

		if(is_dir($userDir)) {
			rmdir($userDir);
		}
	}

	public function setProcessed(Upload $upload, User $user):void {
		$this->db->update("setProcessed", [
			"id" => $upload->id,
			"userId" => $user->id,
		]);
	}

	public function cacheUsage(Upload $upload):void {
		$earning = $this->db->fetchFloat("calculateTotalEarningForUpload", $upload->id);
		$this->db->update("cacheEarning", [
			"uploadId" => $upload->id,
			"earning" => $earning,
		]);
		Log::debug("Caching $earning earning for upload $upload->id");
	}

	/** @return class-string */
	private function detectUploadType(string $uploadedFilePath):string {
		$filePath = $uploadedFilePath;

		$type = UnknownUpload::class;
		$uploadedFileExtension = pathinfo($uploadedFilePath, PATHINFO_EXTENSION);

		if($uploadedFileExtension === "zip") {
// TODO: Unzip the zip and look for known files, then change $filePath to the internal CSV file.
			$filePath = new ZipFileFinder($uploadedFilePath);
		}

		if($uploadedFileExtension === "xlsx") {
			$type = CargoPhysicalUpload::class;
		}
		elseif($this->isCsv($filePath)) {
			$type = $this->detectUploadTypeFromCsv($filePath);
		}
		elseif($this->isTsv($filePath)) {
			$type = $this->detectUploadTypeFromTsv($filePath);
		}

		return $type;
	}

	private function isCsv(string $filePath):bool {
		$fh = fopen($filePath, "r");
		$firstLine = fgets($fh);
		$csvData = str_getcsv($firstLine);

		if(count($csvData) <= 1) {
			return false;
		}

		return true;
	}

	private function isTsv(string $filePath):bool {
		$fh = fopen($filePath, "r");
		$firstLine = fgets($fh);
		$csvData = str_getcsv($firstLine, "\t");

		if(count($csvData) <= 1) {
			return false;
		}

		return true;
	}
	private function hasCsvColumns(
		string $filePath,
		string...$columnsToCheck,
	):bool {
		$firstLine = $this->getCsvLine(fopen($filePath, "r"));
		return $this->allColumnsExist($firstLine, $columnsToCheck);
	}

	private function hasTsvColumns(
		string $filePath,
		string...$columnsToCheck,
	):bool {
		$firstLine = $this->getCsvLine(
			fopen($filePath, "r"),
			"\t",
		);
		return $this->allColumnsExist($firstLine, $columnsToCheck);
	}

	/**
	 * @param resource $fh
	 * @return array<string, string>
	 */
	private function getCsvLine($fh, string $separator = ","):array {
		$line = fgetcsv($fh, separator: $separator);
		foreach($line as $i => $column) {
			$line[$i] = preg_replace(
				'/[[:^print:]]/',
				'',
				$column
			);
		}
		return $line;
	}

	/**
	 * @param array<string, string> $row
	 * @param array<string> $columnsToCheck
	 */
	private function allColumnsExist(array $row, array $columnsToCheck):bool {
		foreach($columnsToCheck as $columnName) {
			if(!in_array($columnName, $row)) {
				return false;
			}
		}

		return true;
	}

// TODO: We probably should always automatically convert to a kvp, otherwise this function is VERY similar to detectUploadTypeFromTsv and potentially others.

	protected function detectUploadTypeFromCsv(mixed $filePath):string {
		$type = UnknownUpload::class;
		if($this->hasCsvColumns($filePath, ...PRSStatementUpload::KNOWN_COLUMNS)) {
			$type = PRSStatementUpload::class;
		}
		elseif($this->hasCsvColumns($filePath, ...BandcampUpload::KNOWN_COLUMNS)) {
			$type = BandcampUpload::class;
		}
		elseif($this->hasCsvColumns($filePath, ...CargoDigitalUpload::KNOWN_COLUMNS)) {
			$type = CargoDigitalUpload::class;
		}
		elseif($this->hasCsvColumns($filePath, ...TuneCoreUpload::KNOWN_COLUMNS)) {
			$type = TuneCoreUpload::class;
		}
		return $type;
	}

	protected function detectUploadTypeFromTsv(mixed $filePath):string {
		$type = UnknownUpload::class;
		if($this->hasTsvColumns($filePath, ...DistroKidUpload::KNOWN_COLUMNS)) {
			$type = DistroKidUpload::class;
		}
		elseif($this->hasTsvColumns($filePath, ...CdBabyUpload::KNOWN_COLUMNS)) {
			$type = CdBabyUpload::class;
		}
		return $type;
	}

	private function rowToUpload(?Row $row):?Upload {
		if(!$row) {
			return null;
		}

		$earnings = new Money();
		if($earningsFloat = $row->getFloat("totalEarningCache")) {
			$earnings = new Money($earningsFloat);
		}

		$processedAt = $row->getDateTime("usagesProcessed");

		/** @var class-string<Upload> $type */
		$type = $row->getString("type");
		return new $type(
			$row->getString("id"),
			$row->getString("filePath"),
			$earnings,
			$processedAt,
		);
	}

	private function ensureCorrectEncoding(string $filePath):void {
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		if($ext === "zip" || $ext === "xlsx") {
			return;
		}

		$fileResult = system("file -bi '$filePath'");

		if(str_contains($fileResult, "charset=utf-8")
			|| str_contains($fileResult, "charset=binary")) {
			return;
		}

		$fileResultParts = explode(";", $fileResult);
		$encodingString = trim($fileResultParts[1]);
		$encodingParts = explode("=", $encodingString);
		$encoding = $encodingParts[1];
		$content = file_get_contents($filePath);

		if($encoding === "us-ascii") {
			$encoding = "ISO-8859-1";
		}

		$content = mb_convert_encoding($content, "UTF-8", $encoding);
		file_put_contents($filePath, $content);
	}

	private function ensureUnixLineEnding(string $filePath):void {
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		if($ext === "zip" || $ext === "xlsx") {
			return;
		}
		$fhIn = fopen($filePath, "r");

		$firstLine = fgets($fhIn, 2048);
		if(!str_contains($firstLine, "\r")) {
			// Everything's OK :)
			return;
		}
		fclose($fhIn);

		$contents = file_get_contents($filePath);
		$contents = str_replace("\r\n", "\n", $contents);
		$contents = str_replace("\r", "\n", $contents);

		file_put_contents("$filePath.fixed", $contents);
		rename("$filePath.fixed", $filePath);
	}

	private function ensureSeparatorMatchesExtension(string $filePath):void {
		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		$extensionSeparators = [
			"csv" => ",",
			"tsv" => "\t",
		];

		if (!array_key_exists($ext, $extensionSeparators) || in_array($ext, ["zip", "xlsx"])) {
			return;
		}

		$oppositeExt = $ext === "csv" ? "tsv" : "csv";
		if ($this->checkFileTypeMismatch($ext, $oppositeExt, $filePath)) {
			$this->convertFile($filePath, $extensionSeparators[$ext], $extensionSeparators[$oppositeExt]);
		}
	}

	private function checkFileTypeMismatch(string $ext, string $oppositeExt, string $filePath): bool {
		$checkFunctions = ["csv" => "isCsv", "tsv" => "isTsv"];
		return !$this->{$checkFunctions[$ext]}($filePath) && $this->{$checkFunctions[$oppositeExt]}($filePath);
	}

	private function convertFile(string $filePath, string $separatorIn, string $separatorOut): void {
		$fhIn = fopen($filePath, "r");
		$fhOut = fopen("$filePath.fixed", "w");

		while(!feof($fhIn)) {
			$line = fgets($fhIn);
			if ($line) {
				$row = str_getcsv($line, $separatorIn);
				fputcsv($fhOut, $row, $separatorOut);
			}
		}

		fclose($fhIn);
		fclose($fhOut);

		rename("$filePath.fixed", $filePath);
	}
}
