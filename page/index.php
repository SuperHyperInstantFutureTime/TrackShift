<?php
use DateTime;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Ulid\Ulid;
use Trackshift\Upload\UploadManager;

function go(
	UploadManager $uploadManager,
	Input $input,
	Response $response,
	HTMLDocument $document,
	DocumentBinder $binder,
):void {
	$uploadManager->purge();

	if($userId = $input->getString("user")) {
		$userDataDir = "data/$userId";

		$statement = $uploadManager->load(...glob("$userDataDir/*.*"));
		$statementCount = $binder->bindList($statement, $document->querySelector("details"));
		$binder->bindKeyValue("uploadCount", $statementCount);

		$expiryDate = $statement->getExpiryDate();
		$dateIn3daysMinus1day = new DateTime("+20 days");
		if($expiryDate > $dateIn3daysMinus1day) {
			$document->querySelector("details button[value=extend]")->hidden = true;
		}
		$binder->bindKeyValue("expiryDateString", $expiryDate?->format("dS M @ h:ia"));
		if($statementCount === 0) {
			$document->querySelector("details")->hidden = true;
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

function do_extend(Input $input, Response $response):void {
	$userId = $input->getString("user");
	if(!$userId) {
		$response->reload();
	}

	$dir = "data/$userId";
	if(is_dir($dir)) {
		$dateIn3weeks = new DateTime("+3 weeks");
		touch($dir, $dateIn3weeks->getTimestamp());
	}

	$response->redirect("./?user=$userId");
}
