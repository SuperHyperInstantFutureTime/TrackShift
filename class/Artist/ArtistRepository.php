<?php
namespace SHIFT\TrackShift\Artist;

use Gt\Database\Result\Row;
use SHIFT\TrackShift\Auth\User;
use SHIFT\TrackShift\Repository\NormalisedString;
use SHIFT\TrackShift\Repository\Repository;

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

	public function getById(string $id, User $user):?Artist {
		return $this->rowToArtist($this->db->fetch("getById", [
			"id" => $id,
			"userId" => $user->id,
		]));
	}

	public function getByName(string $artistName, User $user):?Artist {
		return $this->rowToArtist($this->db->fetch("getArtistByName", $artistName, $user->id));
	}

	public function getByNormalisedName(string $normalisedName, User $user):?Artist {
		return $this->rowToArtist($this->db->fetch("getArtistByNormalisedName", $normalisedName, $user->id));
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

	public function create(User $user, Artist...$artistsToCreate):int {
		$count = 0;
		foreach($artistsToCreate as $artist) {
			$count += $this->db->insert("create", [
				"id" => $artist->id,
				"name" => $artist->name,
				"userId" => $user->id,
				"nameNormalised" => new NormalisedString($artist->name),
			]);
		}

		return $count;
	}

}
