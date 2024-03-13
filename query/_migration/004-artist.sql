create table Artist
(
	id varchar(128) not null primary key,
	name varchar(128) not null,
	nameNormalised varchar(128) not null,
	userId varchar(128) not null,

	foreign key (userId)
		references User(id)
		on update cascade
		on delete cascade
);

create index Artist_nameNormalised_index
    on Artist (nameNormalised);
