<?php
namespace SHIFT\TrackShift\Auth;

use SHIFT\TrackShift\Repository\Entity;

readonly class User extends Entity {
	public function __construct(
		public string $id,
	) {}
}
