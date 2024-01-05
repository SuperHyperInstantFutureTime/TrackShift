<?php
namespace SHIFT\TrackShift\Audit;

use DateTime;
use SHIFT\TrackShift\Audit\AuditItem;
use SHIFT\TrackShift\Auth\User;

readonly class NotificationItem {
	public function __construct(
		public string $html,
		public DateTime $timestamp,
	) {}
}
