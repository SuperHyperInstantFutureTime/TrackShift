<?php
namespace Trackshift\Usage;

use Iterator;
use Trackshift\Artist\Artist;

class ArtistUsage {
	/** @var array<Artist> Distinct list of artists found in aggregation */
	private array $artistList;
	/** @var array<int, UsageList> Key matches key with artist lists */
	private array $artistUsageList;

	public function __construct() {
		$this->artistList = [];
		$this->artistUsageList = [];
	}

	public function addArtistUsage(Artist $artist, Usage $usage):void {
		if(!in_array($artist, $this->artistList, true)) {
			array_push($this->artistList, $artist);
		}

		$index = array_search($artist, $this->artistList);
		if(!isset($this->artistUsageList[$index])) {
			$this->artistUsageList[$index] = new UsageList();
		}

		$this->artistUsageList[$index]->add($usage);
	}

	/** @return array<Artist> */
	public function getAllArtists():array {
		return $this->artistList;
	}

	public function getUsageListForArtist(Artist $artist):UsageList {
		$index = array_search($artist, $this->artistList);
		if($index === false) {
			return new UsageList();
		}
		return $this->artistUsageList[$index];
	}
}
