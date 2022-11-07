<?php
namespace Trackshift\Upload;

abstract class Upload {
	public function __construct(
		public readonly string $filePath,
	) {}
}
