<?php
use Gt\Http\Response;
use Gt\Input\Input;

function do_signup(
	Input $input,
	Response $response,
):void {
	$log = implode("\t", [
		date("Y-m-d H:i:s"),
		$input->getString("name"),
		$input->getString("email"),
	]);
	file_put_contents("data/sign-up.txt", $log . "\n", FILE_APPEND);

	$response->redirect("#signed-up");
}
