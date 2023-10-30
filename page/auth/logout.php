<?php
use Authwave\Authenticator;
use Gt\Http\Response;
use Gt\Session\Session;

function go(
	Authenticator $authenticator,
	Response $response,
	Session $session,
):void {
	$session->kill();
	$authenticator->logout();
	$response->redirect("/");
}
