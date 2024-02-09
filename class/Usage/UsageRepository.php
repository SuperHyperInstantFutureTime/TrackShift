<?php
namespace SHIFT\TrackShift\Usage;

use Gt\Database\Database;
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
use SHIFT\TrackShift\Repository\StringCleaner;
use SHIFT\TrackShift\Upload\Upload;

readonly class UsageRepository extends Repository {
	const UNSORTED_UPC = "::UNSORTED_UPC::";
	const UNSORTED_ISRC = "::UNSORTED_ISRC::";

	/** @return array<Usage> */
	public function createUsagesFromUpload(Upload $upload):array {
		$usageList = [];

		foreach($upload->generateDataRows() as $dataRow) {
			$usage = new Usage(
				new Ulid("usage"),
				$upload,
				$dataRow,
			);
			array_push($usageList, $usage);
			$this->db->insert("create", [
				"id" => $usage->id,
				"uploadId" => $upload->id,
				"data" => json_encode($dataRow),
			]);
		}

		return $usageList;
	}

	/**
	 * @param array<Usage> $usageList
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	// phpcs:ignore
	public function process(
		User $user,
		array $usageList,
		Upload $upload,
		ArtistRepository $artistRepository,
		ProductRepository $productRepository,
	):int {
		$i = null;

		foreach($usageList as $usage) {
			$upload->loadUsageForInternalLookup($usage->row);
		}

		foreach($usageList as $i => $usage) {
			$artistName = new StringCleaner($upload->extractArtistName($usage->row));
			$productTitle = new StringCleaner($upload->extractProductTitle($usage->row));
			$productTitleNormalised = (string)(new NormalisedString($productTitle));
			$earning = $upload->extractEarning($usage->row);

			$artist = $artistRepository->getByName($artistName, $user);
			if(!$artist) {
				$artist = new Artist(
					new Ulid("artist"),
					$artistName,
				);
				$artistRepository->create($user, $artist);
			}

			$product = $productRepository->getByNormalisedTitleAndArtist(
				$productTitleNormalised,
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

			$this->db->insert("assignProductUsage", [
				"id" => (string)(new Ulid("pu")),
				"usageId" => $usage->id,
				"productId" => $product->id,
				"earning" => $earning->value,
			]);
			$productRepository->clearEarningCache($product);
		}

		return is_null($i) ? 0 : $i + 1;
	}
}
