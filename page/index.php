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
	TemplateCollection $templateCollection,
	DocumentBinder $binder,
):void {
	if($userId = $input->getString("user")) {
		$userDataDir = "data/$userId";
		if(!is_dir($userDataDir)) {
			return;
		}

		$uploadManager = new UploadManager();
		$statement = $uploadManager->load(...glob("$userDataDir/*.*"));

		foreach($statement as $upload) {
			$errorFiles = [];

			if($upload instanceof UnknownUpload) {
				array_push($errorFiles, $upload->filePath);
			}

			if($errorFiles) {
				$t = $templateCollection->get($document, "error")->insertTemplate();
				$t->innerText = "ERROR: Unknown file type - " . implode(", ", $errorFiles);
				return;
			}
		}

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

		$binder->bindList($tableData);
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

	$file = $input->getFile("statement");
	$originalFileName = $file->getClientFilename();

	$targetPath = "data/$userId/$originalFileName";
	if(!is_dir(dirname($targetPath))) {
		mkdir(dirname($targetPath), 0775, true);
	}

	$file->moveTo($targetPath);
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
