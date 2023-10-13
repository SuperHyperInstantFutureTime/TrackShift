<?php
namespace SHIFT\Trackshift\Split;

use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;

readonly class SplitRepository extends Repository {
	/** @return array<SplitPercentage> */
	public function getPercentageList(User $user, string $productId):array {
		return [];
	}

}
