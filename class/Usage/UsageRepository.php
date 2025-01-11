<?php
namespace SHIFT\TrackShift\Usage;

use Gt\Database\Database;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\CurrencyExchange;
use SHIFT\TrackShift\Upload\Upload;
use SHIFT\TrackShift\Upload\UploadRepository;

readonly class UsageRepository extends Repository {
	const UNSORTED_UPC = "::UNSORTED_UPC::";
	const UNSORTED_ISRC = "::UNSORTED_ISRC::";

	public function extractFromUploadedFile(
		Upload $upload,
	):int {
		$dbUsageFilePath = "/tmp/trackshift/usages-csv/$upload->id/usage.csv";
		if(!is_dir(dirname($dbUsageFilePath))) {
			mkdir(dirname($dbUsageFilePath), recursive: true);
		}
		$fhUsages = fopen($dbUsageFilePath, "w");

		if($upload::REQUIRES_PRELOADING) {
			Log::debug("Upload requires preloading...");
			foreach($upload->generateDataRows() as $i => $resultSet) {
				$row = $resultSet->first();
				$upload->preloadMissingProductTitleData($row);
			}

			Log::debug("Preloaded $i data rows internally.");
		}

		$csvRowCount = 0;
		foreach($upload->generateDataRows() as $resultSet) {
			$row = $resultSet->first();
			$usageId = (string)(new Ulid("usage"));
			$json = json_encode($resultSet);
			if($error = json_last_error()) {
				Log::critical("Error $error: " . json_last_error_msg(), $row);
			}

			$upload->loadUsageForInternalLookup($row);
			$productTitle = $upload->extractProductTitle($row);

			fputcsv($fhUsages, [
				$usageId,
				$upload->id,
				$json,
				$upload->extractArtistName($row),
				$productTitle,
			]);
			$csvRowCount++;
			Log::debug("Created row $csvRowCount.");
		}

		fclose($fhUsages);
		$this->db->insert("loadUsageFromFile", [
			"infileName" => $dbUsageFilePath,
		]);

		return $csvRowCount;
	}

	public function processArtistsAndProducts(
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
		UserRepository $userRepository,
	):void {
		foreach($this->db->fetchAll("getUniqueUserArtistProducts") as $row) {
			$user = $userRepository->getById($row->getString("userId"));
			$artistName = $row->getString("extractedArtistName");
			$productTitle = $row->getString("extractedProductTitle");

			$artist = $artistRepository->getByName($artistName, $user);
			if(!$artist) {
				$artist = new Artist(
					new Ulid("artist"),
					$artistName,
				);
				$artistRepository->create($user, $artist);
			}

			$product = $productRepository->getByTitleAndArtist($productTitle, $artist, $user);
			if(!$product) {
				$productRepository->create($user, new Product(
					new Ulid("product"),
					$productTitle,
					$artist,
				));
			}
		}
	}

	public function processUsageOfProducts(
		ProductRepository $productRepository,
		ArtistRepository $artistRepository,
		UserRepository $userRepository,
		UploadRepository $uploadRepository,
		Database $db,
	):int {
		$totalInserts = 0;
		$totalProcessed = 0;

		$userCache = [];
		$uploadCache = [];
		$artistCache = [];
		$productCache = [];

		$currencyExchange = new CurrencyExchange();

		$dbUOPPath = "/tmp/trackshift/usage-of-product.csv";
		if(!is_dir(dirname($dbUOPPath))) {
			mkdir(dirname($dbUOPPath), recursive: true);
		}

		$limitPerIteration = 1000;
		$db->executeSql("SET FOREIGN_KEY_CHECKS=0");

		while($unprocessedRows = $this->db->fetchAll("getUnprocessed", ["limit" => $limitPerIteration])) {
			if(count($unprocessedRows) === 0) {
				return $totalProcessed;
			}

			$chunkStartTime = microtime(true);
			$db->executeSql("start transaction");
			$fhUsages = fopen($dbUOPPath, "w");

			foreach($unprocessedRows as $row) {
				$usageId = $row->getString("id");
				$uploadId = $row->getString("uploadId");

				$upload = $uploadCache[$uploadId] ?? null;
				if(!$upload) {
					$upload = $uploadRepository->getById($uploadId);
					$uploadCache[$uploadId] = $upload;
				}
				$userId = $row->getString("userId");
				$user = $userCache[$userId] ?? $userRepository->getById($userId);
				$artistName = $row->getString("extractedArtistName");
				$productTitle = $row->getString("extractedProductTitle");

				$artist = $artistCache["$artistName||$userId"] ?? null;
				if(!$artist) {
					$artist = $artistRepository->getByName($artistName, $user);
					$artistCache["$artistName||$userId"] = $artist;
				}

				$product = $productCache["$productTitle||$artistName||$userId"] ?? null;
				if(!$product) {
					$product = $productRepository->getByTitleAndArtist(
						$productTitle,
						$artist,
						$user,
					);
					$productCache["$productTitle||$artistName||$userId"] = $product;
				}

				$jsonString = $row->getString("data");
				$data = json_decode($jsonString, true);
				if(!$data) {
					$error = json_last_error();
					$errorMessage = json_last_error_msg();
				}
				$earning = $upload->extractEarning($data);
				$earningDate = $upload->extractEarningDate($data);

				$usageOfProduct = [
					"id" => new Ulid("uop"),
					"usageId" => $usageId,
					"productId" => $product->id,
					"earning" => $earning->value,
					"earningDate" => $earningDate->format("Y-m-d H:i:s"),
					"originalEarning" => $earning->value,
					"originalCurrency" => $earning->currency->name,
					"statementType" => $upload->type,
					"estimateAUD" => null,//$currencyExchange->convert($earning, $earningDate, Currency::AUD),
					"estimateCAD" => null,//$currencyExchange->convert($earning, $earningDate, Currency::CAD),
					"estimateEUR" => null,//$currencyExchange->convert($earning, $earningDate, Currency::EUR),
					"estimateGBP" => $currencyExchange->convert($earning, $earningDate, Currency::GBP),
					"estimateUSD" => $currencyExchange->convert($earning, $earningDate, Currency::USD),
					"estimateMXN" => null,//$currencyExchange->convert($earning, $earningDate, Currency::MXN),
					"estimateNZD" => null,//$currencyExchange->convert($earning, $earningDate, Currency::NZD),
				];
				fputcsv($fhUsages, $usageOfProduct);

				$totalProcessed += $this->db->update("setProcessed", $usageId);
			}

			fclose($fhUsages);

			$this->db->insert("loadUsageOfProductFromFile", [
				"infileName" => $dbUOPPath,
			]);
			$totalInserts += $limitPerIteration;

			$db->executeSql("commit");
			$chunkEndTime = microtime(true);
			$chunkDeltaTime = round($chunkEndTime - $chunkStartTime);

			Log::debug("Processed $totalProcessed in $chunkDeltaTime s ($artistName - $productTitle)");
		}

		$db->executeSql("SET FOREIGN_KEY_CHECKS=1");

		return $totalInserts;
	}

	public function generateProductEarnings(
		ProductRepository $productRepository,
	):void {
		$resultSet = $this->db->fetchAll("getUsagesWithoutProductEarnings");

		foreach($resultSet as $row) {
			$productId = $row->getString("productId");
			echo $productId, PHP_EOL;
			$productRepository->storeProductEarning(
				$productId,
				$row->getFloat("earningSum"),
				$row->getDateTime("earningDate")
			);
		}
	}


	/**
	 * @return array<string, array<string, array<int, string>>>
	 *         Outer array key = user id
	 * 	   Outer array value = two lists: artist names, product titles
	 */
	private function getArtistProductLists():array {
		$result = [];

		foreach($this->db->fetchAll("getUnprocessed", ["limit" => 100]) as $row) {
			$userId = $row->getString("userId");
			if(!isset($result[$userId])) {
				$result[$userId] = [
					"artistNameList" => [],
					"productTitleList" => [],
				];
			}

			$uploadType = $row->getString("uploadType");
			/** @var Upload $upload */
			$upload = new $uploadType(
				$row->getString("uploadId"),
				$row->getString("uploadFilePath"),
			);

			$data = json_decode($row->getString("data"), true);
			$artistName = $upload->extractArtistName($data);
			$productTitle = $upload->extractProductTitle($data);

			array_push(
				$result[$userId]["artistNameList"],
				$artistName,
			);
			array_push(
				$result[$userId]["productTitleList"],
				$productTitle,
			);
		}

		return $result;
	}

	public function recalculateCurrencies(
		Currency $newCurrency,
		User $user,
		ProductRepository $productRepository,
	):void {
		$productList = $productRepository->getAll($user);

		// Step 1: Reset cached earnings on Products.
		foreach($productList as $product) {
			$productRepository->clearEarningCache($product);
		}

		$exchange = new CurrencyExchange();
		// Step 2: Recalculate earnings field in UsageOfProduct
		foreach($productList as $product) {
			foreach($this->db->fetchAll("getUnconfirmedUsageOfProduct", $product->id) as $row) {
				$earningValue = match($newCurrency) {
					Currency::EUR => $row->getFloat("estimateEUR"),
					Currency::GBP => $row->getFloat("estimateGBP"),
					Currency::USD => $row->getFloat("estimateUSD"),
				};
				$this->db->update("setEarningForUsageOfProduct", [
					"id" => $row->getString("id"),
					"earning" => $earningValue,
				]);
			}
		}

		$productRepository->calculateUncachedEarnings($user);
	}
}
