<?php
use Gt\DomTemplate\DocumentBinder;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;

function go(DocumentBinder $binder, AuditRepository $auditRepository, User $user):void {
	$binder->bindList($auditRepository->getAll($user));
}
