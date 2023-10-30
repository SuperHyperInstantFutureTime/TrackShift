<?php
use Authwave\Authenticator;
use Gt\Http\Response;

function go(Authenticator $authenticator, Response $response):void {
	$authenticator->logout();
	$response->redirect("/");
}
