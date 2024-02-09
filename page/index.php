<?php
use Gt\Http\Response;
use Gt\Input\Input;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Upload\UploadRepository;

function go(
	UploadRepository $uploadRepository,
	User $user,
	Input $input,
	Response $response,
):void {
	if($uploadRepository->getUploadsForUser($user)) {
		if(!$input->contains("homepage")) {
			$response->redirect("/account/products/");
		}
	}
}
