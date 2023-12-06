<?php
namespace SHIFT\TrackShift\Upload;

use DateTime;
use Gt\Database\Query\QueryCollection;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Audit\AuditRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Money;
use SplFileObject;
use ZipArchive;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
readonly class UploadRepository extends Repository {
	const DIR_UPLOAD = "data/upload";

	public function __construct(
		QueryCollection $db,
		private AuditRepository $auditRepository,
	) {
		parent::__construct($db);
	}

	public function purgeOldFiles(string $dir = self::DIR_UPLOAD):int {
		$count = 0;
		$expiryDate = new DateTime("-3 weeks");

		foreach(glob("$dir/*") as $userDir) {
			foreach(glob("$userDir/*") as $filePath) {
				if(filemtime($filePath) < $expiryDate->getTimestamp()) {
					$count += $this->deleteByFileName($filePath);
				}
			}
		}

		return $count;
	}

	public function setProcessed(Upload $upload):void {
		$this->db->update("setProcessed", $upload->id);
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

			if($upload instanceof UnknownUpload) {
				$this->auditRepository->notify(
					$user,
					"Your latest upload was not processed ($upload->filename)",
					$upload->id,
				);
			}
			else {
				$this->auditRepository->create(
					$user,
					$upload->id,
					$upload->filename,
				);
			}

			array_push($completedUploadList, $upload);
		}

		return $completedUploadList;
	}

	public function clearUserData(User $user):void {
		$this->db->delete("deleteAllForUser", $user->id);
		$userDir = $this->getUserDataDir($user);
		foreach(glob("$userDir/*") as $filePath) {
			unlink($filePath);
		}

		rmdir($userDir);
	}

	/** @return array<Upload> */
	public function getUploadsForUser(User $user):array {
		$uploadList = [];

		foreach($this->db->fetchAll("getForUser", [
			"userId" => $user->id,
		]) as $row) {
			$type = $row->getString("type");
			/** @var Upload $upload */
			$filePath = $row->getString("filePath");
			if(!is_file($filePath)) {
				continue;
			}

			$earning = new Money(0);
			if($earningValue = $row->getFloat("totalEarnings")) {
				$earning = new Money($earningValue);
			}

			$upload = new $type($row->getString("id"), $filePath, $earning);
			array_push(
				$uploadList,
				$upload,
			);
		}

		return $uploadList;
	}

	public function deleteByFileName(string $filePath):int {
		if(is_file($filePath)) {
			unlink($filePath);
		}

		return $this->db->delete("deleteByFilePath", $filePath);
	}


	public function deleteById(User $user, string $id):int {
		return $this->db->delete("delete", [
			"id" => $id,
			"userId" => $user->id,
		]);
	}

	private function getUserDataDir(User $user):string {
		return self::DIR_UPLOAD . "/$user->id";
	}

	/** @return class-string */
	public function detectUploadType(mixed $uploadedFilePath):string {
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
		}
		elseif($this->isTsv($filePath)) {
			if($this->hasTsvColumns($filePath, ...DistroKidUpload::KNOWN_COLUMNS)) {
				$type = DistroKidUpload::class;
			}
			elseif($this->hasTsvColumns($filePath, ...CdBabyUpload::KNOWN_COLUMNS)) {
				$type = CdBabyUpload::class;
			}
		}

		return $type;
	}

	private function isCsv(string $filePath):bool {
		$file = new SplFileObject($filePath);
		$firstLine = $file->fgets();
		$csvData = str_getcsv($firstLine);

		if(!$csvData || count($csvData) <= 1) {
			return false;
		}

		return true;
	}

	private function isTsv(string $filePath):bool {
		$file = new SplFileObject($filePath);
		$firstLine = $file->fgets();
		$csvData = str_getcsv($firstLine, "\t");

		if(!$csvData || count($csvData) <= 1) {
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


	private function ensureCorrectEncoding(string $filePath):void {
		if(pathinfo($filePath, PATHINFO_EXTENSION) === "zip") {
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
		$content = mb_convert_encoding($content, "UTF-8", $encoding);
		file_put_contents($filePath, $content);
	}

	private function ensureSeparatorMatchesExtension(string $filePath):void {
		$extension = pathinfo($filePath, PATHINFO_EXTENSION);
		$extensionSeparators = [
			"csv" => ",",
			"tsv" => "\t",
		];
		if(!in_array($extension, array_keys($extensionSeparators))) {
			return;
		}

		$separatorIn = $extensionSeparators[$extension];
		$separatorOut = $extensionSeparators[$extension];

		if($extension === "csv"
		&& !$this->isCsv($filePath)
		&& $this->isTsv($filePath)) {
			$separatorIn = $extensionSeparators["tsv"];
			$separatorOut = $extensionSeparators["csv"];
		}
		elseif($extension === "tsv"
		&& !$this->isTsv($filePath)
		&& $this->isCsv($filePath)) {
			$separatorIn = $extensionSeparators["csv"];
			$separatorOut = $extensionSeparators["tsv"];
		}
		else {
			return;
		}

		$fhIn = fopen($filePath, "r");
		$fhOut = fopen("$filePath.fixed", "w");

		while(!feof($fhIn)) {
			$line = fgets($fhIn);
			if(!$line) {
				continue;
			}
			$row = str_getcsv($line, $separatorIn);
			fputcsv($fhOut, $row, $separatorOut);
		}

		fclose($fhIn);
		fclose($fhOut);
		rename("$filePath.fixed", $filePath);
	}

	private function ensureUnixLineEnding(string $filePath):void {
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
}
