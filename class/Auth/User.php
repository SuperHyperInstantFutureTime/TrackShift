<?php
namespace SHIFT\Trackshift\Auth;

use SHIFT\Trackshift\Repository\Entity;

readonly class User extends Entity {
	public function __construct(
		public string $id,
	) {}
}
