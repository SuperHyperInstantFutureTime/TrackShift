<?php
namespace Trackshift\Artist;

class ArtistIdentifier {
	/** @var array<string, Artist> */
	private array $artistData;

	public function __construct() {
		$this->artistData = [];
	}

	public function identify(string $identifier, string $class):Artist {
		$key = implode("_", [
			$class,
			$identifier,
		]);

		if(!isset($this->artistData[$key])) {
			$this->artistData[$key] = new Artist($key, $identifier);
		}

		return $this->artistData[$key];
	}
}
