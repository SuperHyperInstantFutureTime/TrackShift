<?php
namespace SHIFT\TrackShift;

use Authwave\Authenticator;
use Gt\Database\Database;
use Gt\Http\Uri;
use Gt\Session\Session;
use Gt\WebEngine\Middleware\DefaultServiceLoader;
use SHIFT\Spotify\SpotifyClient;
use SHIFT\TrackShift\Artist\ArtistRepository;
//use SHIFT\TrackShift\Audit\AuditRepository;
use SHIFT\TrackShift\Auth\Settings;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Auth\UserRepository;
use SHIFT\TrackShift\Content\ContentRepository;
use SHIFT\TrackShift\Cost\CostRepository;
use SHIFT\TrackShift\Product\ProductRepository;
use SHIFT\TrackShift\Split\SplitRepository;
use SHIFT\TrackShift\Upload\UploadRepository;
use SHIFT\TrackShift\Usage\UsageRepository;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceLoader extends DefaultServiceLoader {
//	public function loadAuditRepo():AuditRepository {
//		$database = $this->container->get(Database::class);
//		return new AuditRepository(
//			$database->queryCollection("Audit"),
//			$this->container->get(UserRepository::class),
//		);
//	}

	public function loadContentRepo():ContentRepository {
		return new ContentRepository("data/web-content");
	}

	public function loadUploadRepo():UploadRepository {
		$db = $this->container->get(Database::class);
		return new UploadRepository(
			$db->queryCollection("Upload"),
		);
	}

	public function loadUsageRepo():UsageRepository {
		$db = $this->container->get(Database::class);
		return new UsageRepository(
			$db->queryCollection("Usage"),
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

	public function loadUser():User {
		$userRepo = $this->container->get(UserRepository::class);
		$user = $userRepo->getLoggedInUser();
		if(!$user) {
			$user = $userRepo->createNewUser();
		}

		if(!$userRepo->getById($user->id)) {
			$user = $userRepo->createNewUser();
		}

		$userRepo->persistUser($user);
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
//			$this->container->get(AuditRepository::class),
		);
	}

	public function loadSplitRepository():SplitRepository {
		$database = $this->container->get(Database::class);

		return new SplitRepository(
			$database->queryCollection("Split"),
			$this->container->get(UserRepository::class),
			$this->container->get(ProductRepository::class),
		);
	}

	public function loadSpotify():SpotifyClient {
		$spotifyConfig = $this->config->getSection("spotify");
		return new SpotifyClient(
			$spotifyConfig->getString("client_id"),
			$spotifyConfig->getString("client_secret"),
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

	public function loadSettings():Settings {
		$userRepo = $this->container->get(UserRepository::class);
		$user = $this->container->get(User::class);
		return $userRepo->getUserSettings($user);
	}
}
