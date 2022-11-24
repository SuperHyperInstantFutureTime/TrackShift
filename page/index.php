<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\TemplateCollection;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Ulid\Ulid;
use Trackshift\Upload\UnknownUpload;
use Trackshift\Upload\UploadManager;

function go(
	Input $input,
	Response $response,
	HTMLDocument $document,
	DocumentBinder $binder,
):void {
	if($userId = $input->getString("user")) {
		$userDataDir = "data/$userId";
		if(!is_dir($userDataDir)) {
			return;
		}

		$uploadManager = new UploadManager();
		$statement = $uploadManager->load(...glob("$userDataDir/*.*"));
		$binder->bindList($statement, $document->querySelector("details"));
		$binder->bindKeyValue("uploadCount", count($statement));

		$aggregatedUsages = $statement->getAggregatedUsages("workTitle");
		$tableData = [];
		foreach($aggregatedUsages as $name => $usageList) {
			array_push(
				$tableData, [
					"title" => $name,
					"total" => $usageList->getTotalAmount(),
				]
			);
		}
		usort($tableData, fn($a, $b) => $a["total"]->value < $b["total"]->value);

		$tableEl = $document->querySelector("table");
		$bound = $binder->bindList($tableData, $tableEl);
		if($bound === 0) {
			$tableEl->hidden = true;
		}
	}
	else {
		$response->redirect("./?user=" . new Ulid());
	}
}

function do_upload(Input $input, Response $response):void {
	$userId = $input->getString("user");
	if(!$userId) {
		$response->reload();
	}

	$fileList = $input->getMultipleFile("statement");
	foreach($fileList as $file) {
		$originalFileName = $file->getClientFilename();

		$targetPath = "data/$userId/$originalFileName";
		if(!is_dir(dirname($targetPath))) {
			mkdir(dirname($targetPath), 0775, true);
		}
		$file->moveTo($targetPath);
	}

	$response->redirect("./?user=$userId");
}

function do_clear(Input $input, Response $response):void {
	$userId = $input->getString("user");
	if(!$userId) {
		$response->reload();
	}

	foreach(glob("data/$userId/*.*") as $file) {
		unlink($file);
	}

	$response->redirect("./?user=$userId");
}

function do_delete(Input $input, Response $response):void {
	$userId = $input->getString("user");
	if(!$userId) {
		$response->reload();
	}

	$filename = $input->getString("filename");
	foreach(glob("data/$userId/$filename.*") as $file) {
		unlink($file);
	}

	$response->redirect("./?user=$userId");
}
