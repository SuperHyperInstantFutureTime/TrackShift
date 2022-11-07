<?php
namespace Trackshift\Upload;

class UploadManager {
	public function load(string $filePath):Upload {
		return new UnknownUpload($filePath);
	}
}
