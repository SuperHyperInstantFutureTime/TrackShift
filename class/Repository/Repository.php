<?php
namespace SHIFT\Trackshift\Repository;

use Gt\Database\Query\QueryCollection;

abstract readonly class Repository {
	public function __construct(protected QueryCollection $uploadDb) {}
}
