<?php
namespace SHIFT\TrackShift\Repository;

use Gt\Database\Query\QueryCollection;

abstract readonly class Repository {
	public function __construct(protected QueryCollection $db) {}
}
