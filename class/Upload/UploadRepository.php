<?php
namespace SHIFT\Trackshift\Upload;

use DateTime;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Royalty\Money;
use SplFileObject;

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
	private function detectUploadType(mixed $filePath):string {
		$type = UnknownUpload::class;

		if($this->isCsv($filePath)) {
			if($this->hasCsvColumns($filePath, ...PRSStatementUpload::KNOWN_CSV_COLUMNS)) {
				$type = PRSStatementUpload::class;
			}
			elseif($this->hasCsvColumns($filePath, ...BandcampUpload::KNOWN_CSV_COLUMNS)) {
				$type = BandcampUpload::class;
			}
			elseif($this->hasCsvColumns($filePath, ...CargoUpload::KNOWN_CSV_COLUMNS)) {
				$type = CargoUpload::class;
			}
			elseif($this->hasCsvColumns($filePath, ...TunecoreUpload::KNOWN_CSV_COLUMNS)) {
				$type = TunecoreUpload::class;
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
		string...$columnsToCheck,
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

	private function ensureCorrectEncoding(string $filePath):void {
		$content = file_get_contents($filePath);
		$fileResult = system("file -bi '$filePath'");

		if(str_contains($fileResult, "charset=utf-8")) {
			return;
		}

		$fileResultParts = explode(";", $fileResult);
		$encodingString = trim($fileResultParts[1]);
		$encodingParts = explode("=", $encodingString);
		$encoding = $encodingParts[1];
		$content = mb_convert_encoding($content, "UTF-8", $encoding);
		file_put_contents($filePath, $content);
	}
}
