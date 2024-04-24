<?php
namespace SHIFT\TrackShift\Usage;

use Gt\Ulid\Ulid;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Royalty\Earning;
use SHIFT\TrackShift\Royalty\Money;
use SHIFT\TrackShift\Upload\Upload;

readonly class UsageRepository extends Repository {
	const UNSORTED_UPC = "::UNSORTED_UPC::";
	const UNSORTED_ISRC = "::UNSORTED_ISRC::";

	/**
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	// phpcs:ignore
	public function process(
		User $user,
		Upload $upload,
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
	):int {
		$i = 0;

		$existingArtistList = $artistRepository->getAll($user);
		$existingProductList = $productRepository->getAll($user);

		$artistMap = array_reduce($existingArtistList, function(array $carry, Artist $artist):array {
			$carry[$artist->id] = $artist->name;
			return $carry;
		}, []);
		$combinedArtistNameProductTitleMap = array_reduce($existingProductList, function(array $carry, Product $product):array {
			$carry[$product->id] = $product->artist->name . ":" . $product->title;
			return $carry;
		}, []);

		$dbUsageFilePath = "/tmp/trackshift/usages-csv/$upload->id/usage.csv";
		$dbUOPFilePath = "/tmp/trackshift/usages-csv/$upload->id/usage-of-product.csv";
		if(!is_dir(dirname($dbUsageFilePath))) {
			mkdir(dirname($dbUsageFilePath), recursive: true);
		}
		$fhUsages = fopen($dbUsageFilePath, "w");
		$fhUOP = fopen($dbUOPFilePath, "w");

		foreach($upload->generateDataRows() as $row) {
			$upload->loadUsageForInternalLookup($row);
		}

		$usageOfProductMap = [];

		foreach($upload->generateDataRows() as $row) {
			$artistName = $upload->extractArtistName($row);
			$productTitle = $upload->extractProductTitle($row);
			$money = $upload->extractEarning($row);
			$earningDate = $upload->extractEarningDate($row);
			$combinedArtistNameProductTitle = "$artistName:$productTitle";

			if($existingArtistId = array_search($artistName, $artistMap)) {
				$artistId = $existingArtistId;
				$artist = new Artist(
					$artistId,
					$artistName,
				);
			}
			else {
				$artistId = (string)(new Ulid("artist"));
				$artist = new Artist(
					$artistId,
					$artistName,
				);
				$artistRepository->create($user, $artist);
				$artistMap[$artistId] = $artistName;
			}

			if($existingProductId = array_search($combinedArtistNameProductTitle, $combinedArtistNameProductTitleMap)) {
				$productId = $existingProductId;
				$product = new Product(
					$productId,
					$productTitle,
					$artist,
				);
			}
			else {
				$productId = (string)(new Ulid("product"));
				$product = new Product(
					$productId,
					$productTitle,
					$artist,
				);
				$productRepository->create($user, $product);
				$productMap[(string)$productId] = $productTitle;
				$combinedArtistNameProductTitleMap[(string)$productId] = $combinedArtistNameProductTitle;
			}

			$usageId = (string)(new Ulid("usage"));
			if(!isset($usageOfProductMap[$usageId])) {
				$usageOfProductMap[$usageId] = [];
			}
			if(!isset($usageOfProductMap[$usageId][$productId])) {
				$usageOfProductMap[$usageId][$productId] = [];
			}

			$newEarning = new Earning($earningDate, $money->value);
			array_push($usageOfProductMap[$usageId][$productId], $newEarning);

			fputcsv($fhUsages, [
				$usageId,
				$upload->id,
				json_encode($row),
			]);
			$i++;
		}
		fclose($fhUsages);

		foreach($usageOfProductMap as $usageId => $productMap) {
			/**
			 * @var string $productId
			 * @var array<Earning> $earningArray
			 **/
			foreach($productMap as $productId => $earningArray) {
				foreach($earningArray as $earning) {
					fputcsv($fhUOP, [
						(string)(new Ulid("product_usage")),
						$usageId,
						$productId,
						$earning->value,
						$earning->earningDate->format("Y-m-d"),
					]);
				}
			}
		}
		fclose($fhUOP);

		$this->db->insert("loadUsageFromFile", [
			"infileName" => $dbUsageFilePath,
		]);
		$this->db->insert("loadUsageOfProductFromFile", [
			"infileName" => $dbUOPFilePath,
		]);

		return $i;
	}
}
