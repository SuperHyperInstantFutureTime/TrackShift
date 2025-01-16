<?php
namespace SHIFT\TrackShift\Upload;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use League\Csv\Reader;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Content\FileFixer;
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

			$fixer = new FileFixer();
			$fixer->fix($targetPath);

			$uploadType = $this->detectUploadType($targetPath);

			$extension = pathinfo($targetPath, PATHINFO_EXTENSION);
			if($this->isCsv($targetPath) && $extension !== "csv") {
				rename($targetPath, "$targetPath.csv");
				$targetPath = "$targetPath.csv";
			}
			elseif($this->isTsv($targetPath) && $extension !== "tsv") {
				rename($targetPath, "$targetPath.tsv");
				$targetPath = "$targetPath.tsv";
			}

			Log::debug("Detected upload type: $uploadType");
			/** @var Upload $upload */
			$upload = new $uploadType(new Ulid("upload"), $targetPath);

			$this->db->insert("create", [
				"id" => $upload->id,
				"userId" => $user->id,
				"filePath" => $upload->filePath,
				"type" => $upload::class,
			]);

// TODO: Handle failures better.
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

	public function getById(string $id):?Upload {
		return $this->rowToUpload($this->db->fetch("getById", $id));
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

	public function getTotalPercentageProcessed(User $user):int {
		$total = 0;
		$i = null;

		foreach($this->getUploadsForUser($user) as $i => $upload) {
			$total += $upload->processedPercentage;
		}

		if($i) {
			$total /= $i + 1;
		}

		return $total;
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

	/** @return array<Upload> */
	public function getUnprocessed():array {
		$uploadList = [];

		foreach($this->db->fetchAll("getUnprocessed") as $row) {
			array_push($uploadList, $this->rowToUpload($row));
		}

		return $uploadList;
	}

	public function setProcessed(Upload $upload):void {
		$this->db->update("setProcessed", [
			"id" => $upload->id,
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
			$type = $this->detectUploadTypeFromCsv($filePath, "\t");
		}

		return $type;
	}

	private function isCsv(string $filePath, string $separator = ","):bool {
		$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		if($extension === "xlsx") {
			return false;
		}

		$fh = fopen($filePath, "r");
		$firstLine = fgetcsv($fh, separator: $separator);
		$secondLine = fgetcsv($fh, separator: $separator);

		if(!$firstLine || !$secondLine) {
			return false;
		}

		if(count($firstLine) <= 1) {
			return false;
		}

		return count($firstLine) === count($secondLine);
	}

	private function isTsv(string $filePath):bool {
		return $this->isCsv($filePath, "\t");
	}

	public function cacheEarnings():int {
		$numUpdated = 0;

		foreach($this->db->fetchAll("getUncachedEarnings") as $row) {
			$totalEarning = $this->db->fetchFloat("calculateTotalEarning", $row->getString("id"));
			$numUpdated += $this->db->update("cacheEarning", [
				"uploadId" => $row->getString("id"),
				"totalEarning" => $totalEarning,
			]);
		}

		return $numUpdated;
	}

	public function clearEarningCache(Upload $upload):void {
		$this->db->update("clearEarningCache", $upload->id);
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

	protected function detectUploadTypeFromCsv(mixed $filePath, string $delimiter = ","):string {
		$type = UnknownUpload::class;
		$reader = Reader::createFromPath($filePath);
		$reader->setDelimiter($delimiter);

		$reader->setHeaderOffset(0);
		$header = $reader->getHeader();

		if($this->allColumnsExist($header, PRSStatementUpload::KNOWN_COLUMNS)) {
			$type = PRSStatementUpload::class;
		}
		elseif($this->allColumnsExist($header, BandcampUpload::KNOWN_COLUMNS)) {
			$type = BandcampUpload::class;
		}
		elseif($this->allColumnsExist($header, CargoDigitalUpload::KNOWN_COLUMNS)) {
			$type = CargoDigitalUpload::class;
		}
		elseif($this->allColumnsExist($header, TuneCoreUpload::KNOWN_COLUMNS)) {
			$type = TuneCoreUpload::class;
		}
		elseif($this->allColumnsExist($header, DistroKidUpload::KNOWN_COLUMNS)) {
			$type = DistroKidUpload::class;
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
		/** @var Upload $upload */
		$upload = new $type(
			$row->getString("id"),
			$row->getString("filePath"),
			$earnings,
			$processedAt,
		);

		$usageProcessedPercentage = $this->db->fetchFloat("getProcessedPercentage", [
			"uploadId" => $upload->id,
		]) ?? 0;
		$upload->setProcessedPercentage($usageProcessedPercentage);

		return $upload;
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
}
