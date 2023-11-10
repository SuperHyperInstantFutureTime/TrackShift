<?php
use Gt\DomTemplate\Binder;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;

function go(Binder $binder, AuditRepository $auditRepository, User $user):void {
	$binder->bindList($auditRepository->getAll($user));
	$auditRepository->checkNotifications($user);
}
