<?php
namespace SHIFT\Trackshift\Audit;

use DateTime;
use Gt\DomTemplate\BindGetter;
use Gt\Ulid\Ulid;
use SHIFT\Trackshift\Auth\User;
use SHIFT\Trackshift\Repository\Entity;

readonly class AuditItem extends Entity {
	public function __construct(
		public string $id,
		public User $user,
		public bool $isNotification,
		public ?string $type = null,
		public ?string $description = null,
		public ?string $valueId = null,
		public ?string $valueField = null,
		public ?string $valueFrom = null,
		public ?string $valueTo = null,
	) {}

	#[BindGetter]
	public function getHtml():string {
		[$typeName, $id] = explode("_", $this->valueId);
		$typeName = ucfirst(strtolower($typeName));

		$descriptionOrId = $this->description ?: $id;

		return match($this->type) {
			"create" => "Created new $typeName ($descriptionOrId)",
			"update" => "Updated $typeName ($descriptionOrId)",
			"delete" => "Deleted $typeName ($descriptionOrId)",
			default => "Something happened...",
		};
	}

	#[BindGetter]
	public function getTimestamp():string {
		$ulid = new Ulid(init: $this->id);
		return (new DateTime("@" . round($ulid->getTimestamp() / 1000)))->format("jS M Y");
	}
}
