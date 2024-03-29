<?php
namespace SHIFT\TrackShift\Auth;

class Settings {
	/** @var array<string, string> */
	private array $kvp = [];

	public function set(string $key, string $value):void {
		$this->kvp[$key] = $value;
	}

	public function get(string $key):?string {
		return $this->kvp[$key] ?? null;
	}

	/** @return array<string, string> */
	public function getKvp():array {
		return $this->kvp;
	}
}
