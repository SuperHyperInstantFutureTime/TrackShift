<?php
namespace Trackshift;

use Gt\ServiceContainer\LazyLoad;
use Gt\WebEngine\Middleware\DefaultServiceLoader;
use Trackshift\Upload\UploadManager;

class ServiceLoader extends DefaultServiceLoader {
	#[LazyLoad]
	public function loadUploadManager():UploadManager {
		return new UploadManager();
	}
}
