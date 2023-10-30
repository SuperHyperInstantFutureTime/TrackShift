<?php
namespace SHIFT\Trackshift;

use Authwave\Authenticator;
use Gt\Database\Database;
use Gt\Http\Uri;
use Gt\Session\Session;
use Gt\WebEngine\Middleware\DefaultServiceLoader;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\Trackshift\Artist\ArtistRepository;
use SHIFT\Trackshift\Audit\AuditRepository;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Auth\UserRepository;
use SHIFT\Trackshift\Content\ContentRepository;
use SHIFT\Trackshift\Cost\CostRepository;
use SHIFT\Trackshift\Product\ProductRepository;
use SHIFT\Trackshift\Upload\UploadManager;

class ServiceLoader extends DefaultServiceLoader {
	public function loadAuditRepo():AuditRepository {
		$database = $this->container->get(Database::class);
		return new AuditRepository(
			$database->queryCollection("Audit"),
		);
	}

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
			$this->container->get(AuditRepository::class),
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
			$this->container->get(ArtistRepository::class),
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

	public function loadArtistRepository():ArtistRepository {
		$db = $this->container->get(Database::class);
		return new ArtistRepository(
			$db->queryCollection("Artist")
		);
	}

	public function loadCostRepository():CostRepository {
		$db = $this->container->get(Database::class);
		return new CostRepository(
			$db->queryCollection("Cost"),
			$this->container->get(ProductRepository::class),
			$this->container->get(AuditRepository::class),
		);
	}

	public function loadAuthenticator():Authenticator {
		$config = $this->config->getSection("authwave");
		$session = $this->container->get(Session::class);
		$uri = $this->container->get(Uri::class);

		return new Authenticator(
			$config->getString("key"),
			$uri,
			$config->getString("host"),
			$session->getStore(UserRepository::SESSION_AUTHENTICATOR_STORE_KEY, true),
		);
	}
}
