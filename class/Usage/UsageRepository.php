<?php
namespace SHIFT\Trackshift\Usage;

use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Artist\Artist;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Product\Product;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Repository\Repository;
use SHIFT\Trackshift\Upload\Upload;

readonly class UsageRepository extends Repository {
	/** @return array<Usage> */
	public function createUsagesFromUpload(Upload $upload):array {
		$usageList = [];

		foreach($upload->generateDataRows() as $row) {
			$usage = new Usage(
				new Ulid("usage"),
				$upload,
				$row,
			);
			array_push($usageList, $usage);
			$this->db->insert("create", [
				"id" => $usage->id,
				"uploadId" => $upload->id,
				"data" => json_encode($row),
			]);
		}

		return $usageList;
	}

	/** @param array<Usage> $usageList */
	public function process(
		User $user,
		array $usageList,
		Upload $upload,
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
	):int {
		$importedUsageIdList = [];
		$importedArtistNameList = [];
		$importedProductTitleList = [];
		$importedCombinedArtistNameProductTitleList = [];
		$importedEarningList = [];
		$mapCombinedArtistNameProductTitleToProduct = [];

		$artistList = [];
//		$productList = [];

		foreach($usageList as $usage) {
			$artistName = $upload->extractArtistName($usage->row);
			$productTitle = $upload->extractProductTitle($usage->row);
			$earning = $upload->extractEarning($usage->row);

			array_push($importedUsageIdList, $usage->id);
			array_push($importedArtistNameList, $artistName);
			array_push($importedProductTitleList, $productTitle);
			array_push($importedEarningList, $earning);

			array_push($importedCombinedArtistNameProductTitleList, $artistName . "__" . $productTitle);
			$this->db->update("setProcessed", $usage->id);
		}

		$importedUniqueArtistNameList = array_unique($importedArtistNameList);
		/** @var array<Artist> $toCreateArtistList */
		$toCreateArtistList = [];
		$mapArtistNameToId = [];
		foreach($importedUniqueArtistNameList as $artistName) {
			$artist = $artistRepository->getByName($artistName, $user);
			if(!$artist) {
				$artist = new Artist(
					new Ulid("artist"),
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
			$artist = $artistList[$artistId];

			$product = $productRepository->find($productTitle, $artist);
			if(!$product) {
				$product = new Product(
					new Ulid("product"),
					$productTitle,
					$artist,
				);
				array_push($toCreateProductList, $product);
			}
//			$productList[$product->id] = $product;
			$mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct] = $product;
		}

		$artistCount = $artistRepository->create($user, ...$toCreateArtistList);
		$productCount = $productRepository->create(...$toCreateProductList);

		Log::debug("Created $artistCount artists and $productCount products");

		$count = 0;
		foreach($importedEarningList as $i => $earning) {
			$artistName = $importedArtistNameList[$i];
			$productTitle = $importedProductTitleList[$i];
			$combinedArtistProduct = $artistName . "__" . $productTitle;
			$product = $mapCombinedArtistNameProductTitleToProduct[$combinedArtistProduct];

			$count += $this->db->insert("assignProductUsage", [
				"id" => (string)(new Ulid("pu")),
				"usageId" => $importedUsageIdList[$i],
				"productId" => $product->id,
				"earning" => $earning->value,
			]);
		}

		return $count;
	}
}
