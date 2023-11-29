<?php
use Gt\DomTemplate\Binder;
use SHIFT\TrackShift\Audit\AuditRepository;
use SHIFT\TrackShift\Auth\User;

function go(Binder $binder, AuditRepository $auditRepository, User $user):void {
	$binder->bindList($auditRepository->getAll($user));
	$auditRepository->checkNotifications($user);
}
