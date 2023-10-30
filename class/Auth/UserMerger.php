<?php
namespace SHIFT\Trackshift\Auth;

use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Cost\CostRepository;

class UserMerger {
	public function __construct(
		AuditRepository $auditRepository,
		CostRepository $costRepository,
	) {}
}
