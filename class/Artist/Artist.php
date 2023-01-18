<?php
namespace Trackshift\Artist;

class Artist {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
	) {}
}
