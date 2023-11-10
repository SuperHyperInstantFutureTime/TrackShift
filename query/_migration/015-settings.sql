create table Settings (
	`userId` text not null references User(id) on update cascade on delete cascade,
	key text not null,
	value text,
	primary key (userId, key)
);
