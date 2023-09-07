<?php
namespace SHIFT\Trackshift\Artist;

class Artist {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
	) {}

	public function __toString():string {
		return $this->name;
	}
}
