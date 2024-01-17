<?php
namespace SHIFT\TrackShift\Usage;

use Gt\Database\Query\QueryCollection;
use Gt\Logger\Log;
use Gt\Ulid\Ulid;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\Artist;
use SHIFT\TrackShift\Artist\ArtistRepository;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Product\Product;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Repository\NormalisedString;
use SHIFT\TrackShift\Repository\Repository;
use SHIFT\TrackShift\Upload\Upload;

readonly class UsageRepository extends Repository {
	const UNSORTED_UPC = "::UNSORTED_UPC::";
	const UNSORTED_ISRC = "::UNSORTED_ISRC::";
	const SEPARATOR = "::::::";

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

	/**
	 * @param array<Usage> $usageList
	 * @return array<array<string>> A tuple of artistName:productTitle
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	// phpcs:ignore
	public function process(
		User $user,
		array $usageList,
		Upload $upload,
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
	):array {
		$importedUsageIdList = [];
		$importedArtistNameList = [];
		$importedArtistNameListNormalised = [];
		$importedProductTitleList = [];
		$importedProductTitleListNormalised = [];
		$importedCombinedArtistNameProductTitleListNormalised = [];
		$importedEarningList = [];
		$mapCombinedArtistNameProductTitleToProduct = [];

		$artistList = [];

		foreach($usageList as $usage) {
			$artistName = $upload->extractArtistName($usage->row);
			$artistNameNormalised = (string)(new NormalisedString($artistName));
			$productTitle = $upload->extractProductTitle($usage->row);
			$productTitleNormalised = (string)(new NormalisedString($productTitle));
			$earning = $upload->extractEarning($usage->row);

			array_push($importedUsageIdList, $usage->id);
			array_push($importedArtistNameList, $artistName);
			array_push($importedArtistNameListNormalised, $artistNameNormalised);
			array_push($importedProductTitleList, $productTitle);
			array_push($importedProductTitleListNormalised, $productTitleNormalised);
			array_push($importedEarningList, $earning);

			array_push($importedCombinedArtistNameProductTitleListNormalised, $artistNameNormalised . self::SEPARATOR . $productTitleNormalised);
			$this->db->update("setProcessed", $usage->id);
		}

		$importedUniqueArtistNameList = array_unique($importedArtistNameList);
		$importedUniqueArtistNameListNormalised = array_unique($importedArtistNameListNormalised);
		/** @var array<Artist> $toCreateArtistList */
		$toCreateArtistList = [];
		$mapArtistNameNormalisedToId = [];
		foreach($importedUniqueArtistNameListNormalised as $i => $artistNameNormalised) {
			$artistName = $importedUniqueArtistNameList[$i];
			$artist = $artistRepository->getByNormalisedName($artistNameNormalised, $user);
			if(!$artist) {
				$artist = new Artist(
					new Ulid("artist"),
					$artistName,
				);
				array_push($toCreateArtistList, $artist);
			}

			$artistList[$artist->id] = $artist;
			$mapArtistNameNormalisedToId[$artistNameNormalised] = $artist->id;
		}

		$importedUniqueCombinedArtistNameProductTitleListNormalised = array_unique($importedCombinedArtistNameProductTitleListNormalised);
		/** @var array<Product> $toCreateProductList */
		$toCreateProductList = [];
		foreach($importedUniqueCombinedArtistNameProductTitleListNormalised as $i => $combinedArtistProductNormalised) {
			[$artistNameNormalised, $productTitleNormalised] = explode(self::SEPARATOR, $combinedArtistProductNormalised);
			$artistId = $mapArtistNameNormalisedToId[$artistNameNormalised] ?? null;
			if(!$artistId) {
				continue;
			}

			$artist = $artistList[$artistId];

			$productTitle = $importedProductTitleList[$i];
			$product = $productRepository->find($productTitleNormalised, $artist, true);
			if(!$product) {
				$product = new Product(
					new Ulid("product"),
					$productTitle,
					$artist,
				);
				array_push($toCreateProductList, $product);
			}
//			$productList[$product->id] = $product;
			$mapCombinedArtistNameProductTitleToProduct[$combinedArtistProductNormalised] = $product;
		}

		$artistCount = $artistRepository->create($user, ...$toCreateArtistList);
		$productCount = $productRepository->create(...$toCreateProductList);

		Log::debug("Created $artistCount artists and $productCount products");

		foreach($importedEarningList as $i => $earning) {
			$artistNameNormalised = $importedArtistNameListNormalised[$i];
			$productTitleNormalised = $importedProductTitleListNormalised[$i];
			$combinedArtistProductNormalised = $artistNameNormalised . self::SEPARATOR . $productTitleNormalised;
			$product = $mapCombinedArtistNameProductTitleToProduct[$combinedArtistProductNormalised] ?? null;

			if(!$product) {
				continue;
			}

			$this->db->insert("assignProductUsage", [
				"id" => (string)(new Ulid("pu")),
				"usageId" => $importedUsageIdList[$i],
				"productId" => $product->id,
				"earning" => $earning->value,
			]);
		}

		return [$importedArtistNameList, $importedProductTitleList];
	}
}
