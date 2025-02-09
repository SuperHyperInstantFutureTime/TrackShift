<?php
namespace SHIFT\TrackShift\Usage;

use DateTime;
use Gt\Database\Database;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Cost\Cost;
use SHIFT\TrackShift\Cost\CostRepository;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\DatabaseTransaction;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Currency;
use SHIFT\TrackShift\Royalty\CurrencyExchange;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Upload\Upload;
use SHIFT\TrackShift\Upload\UploadRepository;

readonly class UsageRepository extends Repository {
	const UNSORTED_UPC = "::UNSORTED_UPC::";
	const UNSORTED_ISRC = "::UNSORTED_ISRC::";

	public function extractProductsFromUpload(
		Upload $upload,
		DatabaseTransaction $transaction,
	):int {
		$transaction->start("Extracting products from upload " . $upload->basename);
		$dbUsageFilePath = "/tmp/trackshift/usages-csv/$upload->id/usage.csv";
		if(!is_dir(dirname($dbUsageFilePath))) {
			mkdir(dirname($dbUsageFilePath), recursive: true);
		}
		$fhUsages = fopen($dbUsageFilePath, "w");

		if($upload::REQUIRES_PRELOADING) {
			Log::debug("Upload requires preloading...");
			foreach($upload->generateDataRows() as $i => $row) {
				$upload->preloadMissingProductTitleData($row);
			}

			Log::debug("Preloaded $i data rows internally.");
		}

		$csvRowCount = 0;
		foreach($upload->generateDataRows() as $row) {
			$usageId = (string)(new Ulid("usage"));
/** Currently on a wild goose chase looking for why the CSV is loading rows and columns with the
 * same value. This following line never triggers!
 * Once I've figured this out, I need to comb through the other types again - TSV will be an easy fix
 * back to fgetcsv, but then I've got to make sure the files are passed through the filters.
 * If all goes tits up, I'll revert to using League's CSV, although we were getting a data cut-off
 * with that, and I wanted to have more control over the raw data so I could inspect it.
 */
//			if($row["currency"] === "currency") {
//				die("WHAT???");
//			}
			$json = json_encode($row);
			if($error = json_last_error()) {
				Log::critical("Error $error: " . json_last_error_msg(), $row);
			}

			// This line is currently only DistroKid-speciifc:
			$upload->loadUsageForInternalLookup($row);

			$artistName = $upload->extractArtistName($row);
			$productTitle = $upload->extractProductTitle($row);

			fputcsv($fhUsages, [
				$usageId,
				$upload->id,
				base64_encode($json),
				$artistName,
				$productTitle,
			]);
			$csvRowCount++;
		}

		fclose($fhUsages);
		$this->db->insert("loadUsageFromFile", [
			"infileName" => $dbUsageFilePath,
		]);
		$this->db->update("base64DecodeJson");

		$transaction->commit();
		return $csvRowCount;
	}

	public function processUsageOfProducts(
		ProductRepository $productRepository,
		ArtistRepository $artistRepository,
		UserRepository $userRepository,
		UploadRepository $uploadRepository,
		CostRepository $costRepository,
		DatabaseTransaction $transaction,
	):int {
		$totalInserts = 0;
		$totalProcessed = 0;

		$userCache = [];
		$uploadCache = [];
		$artistCache = [];
		$productCache = [];

// $costMap is a multidimensional array of [$productTitle||$artistName||$userId](string):[DATE](string):[COST](float)
		$costMap = [];

		$currencyExchange = new CurrencyExchange();

		$dbUOPPath = "/tmp/trackshift/usage-of-product.csv";
		if(!is_dir(dirname($dbUOPPath))) {
			mkdir(dirname($dbUOPPath), recursive: true);
		}

		$limitPerIteration = 1000;

		while($unprocessedRows = $this->db->fetchAll("getUnprocessed", ["limit" => $limitPerIteration])) {
			if(count($unprocessedRows) === 0) {
				break;
			}

			$transaction->startWithoutRelations("Processing usages");

			$chunkStartTime = microtime(true);
			$fhUsages = fopen($dbUOPPath, "w");

			$artistName = "|||UNPROCESSED|||";
			$productTitle = "|||UNPROCESSED|||";

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
				$userCache[$user->id] = $user;

				$jsonString = $row->getString("data");
				$data = json_decode($jsonString, true);
				if(!$data) {
					$error = json_last_error();
					$errorMessage = json_last_error_msg();
					Log::critical("JSON error $error: $errorMessage", [$jsonString]);
				}

				$artistName = $row->getString("extractedArtistName");
				$productTitle = $row->getString("extractedProductTitle");

				if(!$artistName || !$productTitle) {
// Some usage rows do not have any data in them, such as Bandcamp payout rows.
					$totalProcessed += $this->db->update("setProcessed", $usageId);
					continue;
				}

				$artist = $artistCache["$artistName||$userId"] ?? null;
				if(!$artist) {
					$artist = $artistRepository->getByName($artistName, $user);
					if(!$artist) {
						$artist = new Artist(
							new Ulid("artist"),
							$artistName,
						);
						$artistRepository->create($user, $artist);
					}
					$artistCache["$artistName||$userId"] = $artist;
				}

				$productCacheKey = "$productTitle||$artistName||$userId";
				$product = $productCache[$productCacheKey] ?? null;
				if(!$product) {
					$product = $productRepository->getByTitleAndArtist(
						$productTitle,
						$artist,
						$user,
					);
					if(!$product) {
						$product = new Product(
							new Ulid("product"),
							$productTitle,
							$artist,
						);
						$productRepository->create($user, $product);
					}
					$productCache[$productCacheKey] = $product;
				}

				$earning = $upload->extractEarning($data);
				$earningDate = $upload->extractEarningDate($data);

				$type = $upload->getUsageType($data);
				if($type === UsageType::COST) {
					if(!isset($costMap[$productCacheKey])) {
						$costMap[$productCacheKey] = [];
					}
					if(!isset($costMap[$productCacheKey][$earningDate->format("Y-m-d")])) {
						$costMap[$productCacheKey][$earningDate->format("Y-m-d")] = [];
					}

					$costDescription = $upload->getCostDescription($data);
					if(str_starts_with($costDescription, "Cinram Storage")) {
						$costDescription = "Cinram Storage";
					}

					if(!isset($costMap[$productCacheKey][$earningDate->format("Y-m-d")][$costDescription])) {
						$costMap[$productCacheKey][$earningDate->format("Y-m-d")][$costDescription] = 0;
					}

					$costMap[$productCacheKey][$earningDate->format("Y-m-d")][$costDescription] += $earning->value;
				}
				else {
					$usageOfProduct = [
						"id" => new Ulid("uop"),
						"usageId" => $usageId,
						"productId" => $product->id,
						"earning" => $earning->value,
						"earningDate" => $earningDate->format("Y-m-d H:i:s"),
						"originalEarning" => $earning->value,
						"originalCurrency" => $earning->currency?->name,
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
				}

				$totalProcessed += $this->db->update("setProcessed", $usageId);
			}

			fclose($fhUsages);

			$this->db->insert("loadUsageOfProductFromFile", [
				"infileName" => $dbUOPPath,
			]);
			$totalInserts += $totalProcessed;

			$chunkEndTime = microtime(true);

			$chunkDeltaTime = number_format($chunkEndTime - $chunkStartTime, 2);
			$transaction->commit();
			Log::debug("Chunk processed $totalProcessed ($artistName - $productTitle) $chunkDeltaTime seconds");
		}

//					$cost = new Cost(
//						new Ulid("cost"),
//						$product,
//						$upload->getCostDescription($data),
//						$earning,
//						$earningDate,
//					);
//					$costRepository->create($cost, $user);
		foreach($costMap as $productCacheKey => $costDateMap) {
			foreach($costDateMap as $dateString => $costDataMap) {
				foreach($costDataMap as $title => $amount) {
					$product = $productCache[$productCacheKey];
					$cost = new Cost(
						new Ulid("cost"),
						$product,
						$title,
						new Money($amount),
						new DateTime($dateString),
					);
					$costRepository->create($cost, $user);
				}
			}
		}

		return $totalInserts;
	}

	private function setProcessedProductEarnings(string $id):void {
		$this->db->update(
			"setProcessedProductEarnings",
			$id,
		);
	}

	public function calculateProductEarnings(
		ProductRepository $productRepository,
		DatabaseTransaction $transaction,
	):int {
		$numCalculated = 0;
		$resultSet = $this->db->fetchAll("getUsagesWithoutProductEarnings");

		$productEarningsByDate = [];

		$transaction->startWithoutRelations("Calculating product earnings");
		foreach($resultSet as $row) {

			$usageId = $row->getString("id");
			$productId = $row->getString("productId");
			$date = $row->getString("earningDate");
			$earning = $row->getFloat("earning");

			if(!isset($productEarningsByDate[$productId])) {
				$productEarningsByDate[$productId] = [];
			}
			if(!isset($productEarningsByDate[$productId][$date])) {
				$productEarningsByDate[$productId][$date] = [];
			}

			array_push(
				$productEarningsByDate[$productId][$date],
				$earning,
			);
			$numCalculated ++;

			$this->setProcessedProductEarnings($usageId);
		}

		foreach($productEarningsByDate as $productId => $dateArray) {
			foreach(array_keys($dateArray) as $date) {
				$totalEarning = array_sum($dateArray[$date]);
				$productRepository->storeProductEarning(
					$productId,
					$totalEarning,
					new DateTime($date),
				);
			}
		}

		$transaction->commit();
		return $numCalculated;
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
		UploadRepository $uploadRepository,
		DatabaseTransaction $transaction,
	):void {
		$productList = $productRepository->getAll($user);

		$transaction->start();

		// Step 1: Reset cached earnings on Products.
		foreach($productList as $product) {
			$productRepository->clearEarningCache($product);
		}
		foreach($uploadRepository->getUploadsForUser($user) as $upload) {
			$uploadRepository->clearProfitCache($upload);
		}

		$exchange = new CurrencyExchange();
		// Step 2: Recalculate earnings field in UsageOfProduct
		foreach($productList as $product) {
			foreach($this->db->fetchAll("getUnconfirmedUsageOfProduct", $product->id) as $row) {
				$earningValue = match($newCurrency) {
					Currency::AUD => $row->getFloat("estimateAUD"),
					Currency::CAD => $row->getFloat("estimateCAD"),
					Currency::EUR => $row->getFloat("estimateEUR"),
					Currency::GBP => $row->getFloat("estimateGBP"),
					Currency::USD => $row->getFloat("estimateUSD"),
					Currency::MXN => $row->getFloat("estimateMXN"),
					Currency::NZD => $row->getFloat("estimateNZD"),
				};
				$this->db->update("setEarningForUsageOfProduct", [
					"id" => $row->getString("id"),
					"earning" => $earningValue,
				]);
			}
		}

		$productRepository->calculateUncachedEarnings($user);

		$transaction->commit();
	}
}
