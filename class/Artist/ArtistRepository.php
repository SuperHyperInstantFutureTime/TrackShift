<?php
namespace SHIFT\Trackshift\Artist;

use Gt\Database\Result\Row;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Repository;

readonly class ArtistRepository extends Repository {
	/** @return array<Artist> */
	public function getAll(User $user):array {
		$artistArray = [];

		foreach($this->db->fetchAll("getAllForUser", $user->id) as $row) {
			array_push(
				$artistArray,
				$this->rowToArtist($row),
			);
		}

		return $artistArray;
	}

	public function getById(string $id):?Artist {
		return $this->rowToArtist($this->db->fetch("getById", $id));
	}


	private function rowToArtist(?Row $row):?Artist {
		if(!$row) {
			return null;
		}

		return new Artist(
			$row->getString("id"),
			$row->getString("name"),
		);
	}
}
