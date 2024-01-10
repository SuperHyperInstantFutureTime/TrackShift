<?php
namespace SHIFT\TrackShift\Artist;

class Artist {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly string $nameNormalised,
	) {}

	public function __toString():string {
		return $this->name;
	}
}
