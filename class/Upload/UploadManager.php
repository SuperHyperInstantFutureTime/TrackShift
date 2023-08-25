<?php
namespace SHIFT\Trackshift\Upload;

use DateInterval;
use DateTime;
use DateTimeZone;
use Gt\Database\Query\QueryCollection;
use Gt\Database\Result\Row;
use Gt\Input\InputData\Datum\FileUpload;
use Gt\Ulid\Ulid;
use SHIFT\Spotify\Entity\AlbumType;
use SHIFT\Spotify\Entity\EntityType;
use SHIFT\Spotify\Entity\SearchFilter;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Product\ProductEarning;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Royalty\Money;
use SHIFT\Trackshift\Usage\Usage;
use SplFileObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
readonly class UploadManager extends Repository {
	public function __construct(
		QueryCollection $db,
		protected QueryCollection $usageDb,
		protected QueryCollection $artistDb,
		protected QueryCollection $productDb,
	) {
		parent::__construct($db);
	}

	/** @return array<string> List of file names that have been uploaded */
	public function upload(User $user, FileUpload...$uploadList):array {
		$filePathList = [];

		$userDir = $this->getUserDataDir($user);
		foreach($uploadList as $file) {
			$originalFileName = $file->getClientFilename();

			$targetPath = "$userDir/$originalFileName";
			if(!is_dir(dirname($targetPath))) {
				mkdir(dirname($targetPath), 0775, true);
			}
			$file->moveTo($targetPath);
			array_push($filePathList, $targetPath);
		}

		foreach($filePathList as $filePath) {
			$uploadType = $this->detectUploadType($filePath);
			/** @var Upload $upload */
			$upload = new $uploadType(new Ulid(), $filePath);

			if($this->uploadDb->fetch("findByFilePath", $filePath)) {
				continue;
			}

			$this->uploadDb->insert("create", [
				"id" => $upload->id,
				"userId" => $user->id,
				"filePath" => $upload->filePath,
				"type" => $upload::class,
			]);
		}

		return $filePathList;
	}

	/** @return array<Upload> */
	public function getUploadsForUser(User $user):array {
		$uploadList = [];

		foreach($this->uploadDb->fetchAll("getForUser", [
			"userId" => $user->id,
		]) as $row) {
			$type = $row->getString("type");
			array_push(
				$uploadList,
				new $type($row->getString("id"), $row->getString("filePath")),
			);
		}

		return $uploadList;
	}

	public function getNextUploadNotYetProcessed():?Upload {
		if($row = $this->uploadDb->fetch("getSingleUnprocessed")) {
			return $this->rowToUpload($row);
		}

		return null;
	}

	public function processUploadIntoUsages(Upload $upload):void {
		foreach($upload->generateDataRows() as $row) {
			$usage = new Usage(
				new Ulid(),
				$upload,
				$row,
			);
			$this->usageDb->insert("create", [
				"id" => $usage->id,
				"uploadId" => $upload->id,
				"data" => json_encode($row),
			]);
		}

		$this->uploadDb->update("setProcessed", $upload->id);
	}

	public function processUsages(Upload $upload):int {
		$importedUsageIdList = [];
		$importedArtistNameList = [];
		$importedProductTitleList = [];
		$importedCombinedArtistNameProductTitleList = [];
		$importedEarningList = [];
		$mapCombinedArtistNameProductTitleToProduct = [];

		$artistList = [];
		$productList = [];

		foreach($this->usageDb->fetchAll("getUnprocessedForUpload", $upload->id) as $usageRow) {
			$dataRow = json_decode($usageRow->getString("data"), true);
			$artistName = $upload->extractArtistName($dataRow);
			$productTitle = $upload->extractProductTitle($dataRow);
			$earning = $upload->extractEarning($dataRow);

			array_push($importedUsageIdList, $usageRow->getString("id"));
			array_push($importedArtistNameList, $artistName);
			array_push($importedProductTitleList, $productTitle);
			array_push($importedEarningList, $earning);

			array_push($importedCombinedArtistNameProductTitleList, $artistName . "__" . $productTitle);
			$this->usageDb->update("setProcessed", $usageRow->getString("id"));
		}

		$importedUniqueArtistNameList = array_unique($importedArtistNameList);
		/** @var array<Artist> $toCreateArtistList */
		$toCreateArtistList = [];
		$mapArtistNameToId = [];
		foreach($importedUniqueArtistNameList as $artistName) {
			$artist = $this->rowToArtist($this->artistDb->fetch("getArtistByName", $artistName));
			if(!$artist) {
				$artist = new Artist(
					new Ulid(),
					$artistName,
				);
				array_push($toCreateArtistList, $artist);
			}

			$artistList[$artist->id] = $artist;
			$mapArtistNameToId[$artistName] = $artist->id;
		}

		$importedUniqueCombinedArtistNameProductTitleList = array_unique($importedCombinedArtistNameProductTitleList);
		/** @var array<Product> $toCreateProductList */
		$toCreateProductList = [];
		foreach($importedUniqueCombinedArtistNameProductTitleList as $combinedArtistProduct) {
			[$artistName, $productTitle] = explode("__", $combinedArtistProduct);
			$artistId = $mapArtistNameToId[$artistName];

			$product = $this->rowToProduct($this->productDb->fetch("getProductByTitleAndArtist", [
				"title" => $productTitle,
				"artistId" => $artistId,
			]), $artistList[$artistId]);
			if(!$product) {
				$product = new Product(
					new Ulid(),
					$productTitle,
					$artistList[$artistId],
				);
				array_push($toCreateProductList, $product);
			}
			$productList[$product->id] = $product;
			$mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct] = $product;
		}

		foreach($toCreateArtistList as $artist) {
			$this->artistDb->insert("create", [
				"id" => $artist->id,
				"name" => $artist->name,
			]);
		}

		foreach($toCreateProductList as $product) {
			$this->productDb->insert("create", [
				"id" => $product->id,
				"artistId" => $product->artist->id,
				"title" => $product->title,
			]);
		}

		foreach($importedEarningList as $i => $earning) {
			$artistName = $importedArtistNameList[$i];
			$productTitle = $importedProductTitleList[$i];
			$combinedArtistProduct = $artistName . "__" . $productTitle;
			$product = $mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct];

			$this->usageDb->insert("assignProductUsage", [
				"id" => (string)(new Ulid()),
				"usageId" => $importedUsageIdList[$i],
				"productId" => $product->id,
				"earning" => $earning->value,
			]);
		}

		$count = 0;
		return $count;
	}

	public function delete(User $user, string $id):void {
		$row = $this->uploadDb->fetch("getById", [
			"id" => $id,
			"userId" => $user->id,
		]);

		if(!$row) {
			throw new UploadNotFoundException("id: $id");
		}

		$this->uploadDb->delete("delete", [
			"id" => $id,
			"userId" => $user->id,
		]);

		$filePath = $row->getString("filePath");
		if(is_file($filePath)) {
			unlink($filePath);
		}
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

	/** @return array<Product> */
	public function processUploads(User $user, string...$fileNameList):array {
		set_time_limit(0);
		$productList = [];

		foreach($fileNameList as $filePath) {
// TODO: extract into factory?
			$uploadType = $this->detectUploadType($filePath);
			/** @var Upload $upload */
			$upload = new $uploadType(new Ulid(), $filePath);

			if($this->uploadDb->fetch("findByFilePath", $filePath)) {
				continue;
			}

			$this->uploadDb->insert("create", [
				"id" => $upload->id,
				"userId" => $user->id,
				"filePath" => $upload->filePath,
				"type" => $upload::class,
			]);

			$usageRowsToInsert = [];
			foreach($upload->generateDataRows() as $row) {
				$artistName = $upload->extractArtistName($row);
				$productName = $upload->extractProductTitle($row);
				$earning = $upload->extractEarning($row);

				$usage = new Usage(
					new Ulid(),
					$upload,
					$row,
				);
				array_push($usageRowsToInsert, [
					"id" => $usage->id,
					"uploadId" => $upload->id,
					"data" => json_encode($row),
				]);


//				$artist = $this->findOrCreateArtist($artistName);
//				$product = $this->findOrCreateProduct($productName, $artist);
//				array_push($productList, $product);

//				$this->assignUsage($product, $usage, $earning);
			}

			$this->usageDb->insert("createMultiple", [
				"__dynamicValueSet" => $usageRowsToInsert,
			]);
		}

		return $productList;
	}

	public function cacheArt(SpotifyClient $spotify, Product...$productList):void {
		foreach($productList as $product) {
			$cacheFilePath = "data/cache/art/$product->id";
			if(file_exists($cacheFilePath)) {
				continue;
			}

			$results = $spotify->search->query(
				"{$product->artist->name}, $product->title",
				new SearchFilter(EntityType::album)
			);
			if($results->albums->total > 0) {
				if($image = $results->albums->items[0]?->images[0] ?? null) {
					if(!is_dir(dirname($cacheFilePath))) {
						mkdir(dirname($cacheFilePath), recursive: true);
					}
					file_put_contents($cacheFilePath, file_get_contents($image->url));
				}
			}
		}
	}

	private function findOrCreateArtist(string $artistName):Artist {
		if($row = $this->artistDb->fetch("getArtistByName", $artistName)) {
			return new Artist(
				$row->getString("id"),
				$row->getString("name")
			);
		}

		$artist = new Artist(
			new Ulid(),
			$artistName,
		);
		$this->artistDb->insert("create", [
			"id" => $artist->id,
			"name" => $artistName,
		]);
		return $artist;
	}

	private function findOrCreateProduct(string $productTitle, Artist $artist):Product {
		if($row = $this->productDb->fetch("getProductByTitleAndArtist", [
			"title" => $productTitle,
			"artistId" => $artist->id,
		])) {
			return new Product(
				$row->getString("id"),
				$row->getString("title"),
				$artist,
			);
		}

		$product = new Product(
			new Ulid(),
			$productTitle,
			$artist,
		);
		$this->productDb->insert("create", [
			"id" => $product->id,
			"artistId" => $artist->id,
			"title" => $productTitle,
			"type" => "Unset", // TODO: Handle the type (track,album,etc)
		]);
		return $product;
	}

	private function assignUsage(Product $product, Usage $usage, Money $earning):void {
		$this->usageDb->insert("assignProductUsage", [
			"id" => new Ulid(),
			"usageId" => $usage->id,
			"productId" => $product->id,
			"earning" => $earning->value,
		]);
	}

	public function purgeOldFiles(string $dir = "data"):int {
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
		return "data/upload/$user->id";
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

	private function rowToUpload(?Row $row):?Upload {
		if(!$row) {
			return null;
		}

		$filePath = $row->getString("filePath");
		$uploadType = $this->detectUploadType($filePath);
		/** @var Upload $upload */
		$upload = new $uploadType(
			$row->getString("id") ?? new Ulid(),
			$filePath
		);
		return $upload;
	}

	private function rowToArtist(?Row $row):?Artist {
		if(!$row) {
			return null;
		}

		return new Artist(
			$row->getString("id"),
			$row->getString("name"),
		);
	}

	private function rowToProduct(?Row $row, ?Artist $artist):?Product {
		if(!$row) {
			return null;
		}

		return new Product(
			$row->getString("id"),
			$row->getString("title"),
			$artist // ?? $this->getArtistById($row->getString("artistId")
		);
	}


}
