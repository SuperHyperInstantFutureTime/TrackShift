<?php
namespace SHIFT\Trackshift;

use Gt\Database\Database;
use Gt\Session\Session;
use Gt\WebEngine\Middleware\DefaultServiceLoader;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Upload\UploadManager;

class ServiceLoader extends DefaultServiceLoader {
	public function loadContentRepo():ContentRepository {
		return new ContentRepository("data/web-content");
	}

	public function loadUploadManager():UploadManager {
		$db = $this->container->get(Database::class);
		return new UploadManager(
			$db->queryCollection("Upload"),
			$db->queryCollection("Usage"),
			$db->queryCollection("Artist"),
			$db->queryCollection("Product"),
		);
	}

	public function loadUserRepository():UserRepository {
		$db = $this->container->get(Database::class);
		$session = $this->container->get(Session::class);

		return new UserRepository(
			$db->queryCollection("User"),
			$session->getStore(UserRepository::SESSION_STORE_KEY, true)
		);
	}

	public function loadProductRepository():ProductRepository {
		$db = $this->container->get(Database::class);

		return new ProductRepository(
			$db->queryCollection("Product"),
		);
	}

	public function loadSpotify():SpotifyClient {
		$spotifyConfig = $this->config->getSection("spotify");
		return new SpotifyClient(
			$spotifyConfig->getString("client_id"),
			$spotifyConfig->getString("client_secret"),
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
