<?php
namespace Trackshift\Usage;

use Iterator;
use Trackshift\Artist\Artist;

class ArtistUsage {
	/** @var array<Artist> Distinct list of artists found in aggregation */
	private array $artistList;
	/** @var array<string, UsageList> Key matches key with artist lists */
	private array $artistUsageList;

	public function __construct() {
		$this->artistList = [];
		$this->artistUsageList = [];
	}

	public function addArtistUsage(Artist $artist, Usage $usage):void {
		if(!isset($this->artistList[$artist->id])) {
			$this->artistList[$artist->id] = $artist;
		}

		if(!isset($this->artistUsageList[$artist->id])) {
			$this->artistUsageList[$artist->id] = new UsageList();
		}

		$this->artistUsageList[$artist->id]->add($usage);
	}

	/** @return array<Artist> */
	public function getAllArtists():array {
		return $this->artistList;
	}

	public function getUsageListForArtist(Artist $artist):UsageList {
		return $this->artistUsageList[$artist->id] ?? new UsageList();
	}
}
