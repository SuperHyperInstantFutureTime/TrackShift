create table Settings (
	`userId` varchar(128) not null,
	`key` varchar(128) not null,
	`value` varchar(256),

	primary key (`userId`, `key`),
	foreign key (`userId`)
		references User(`id`)
		on delete cascade
		on update cascade
);
