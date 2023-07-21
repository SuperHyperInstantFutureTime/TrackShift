<?php
namespace SHIFT\Trackshift;

use Gt\Session\Session;
use Gt\WebEngine\Middleware\DefaultServiceLoader;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Upload\UploadManager;

class ServiceLoader extends DefaultServiceLoader {
	public function loadContentRepo():ContentRepository {
		return new ContentRepository("data/web-content");
	}

	public function loadUploadManager():UploadManager {
		return new UploadManager();
	}

	public function loadUserRepo():UserRepository {
		$session = $this->container->get(Session::class);

		return new UserRepository(
			$session->getStore(UserRepository::SESSION_STORE_KEY, true)
		);
	}

	public function loadUser():User {
		$userRepo = $this->container->get(UserRepository::class);
		$user = $userRepo->getLoggedInUser();
		if(!$user) {
			$user = $userRepo->createNewUser();
		}

		return $user;
	}
}
