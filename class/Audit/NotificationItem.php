<?php
namespace SHIFT\Trackshift\Audit;

use DateTime;
use SHIFT\Trackshift\Audit\AuditItem;
use SHIFT\Trackshift\Auth\User;

readonly class NotificationItem {
	public function __construct(
		public string $html,
		public DateTime $timestamp,
	) {}
}
