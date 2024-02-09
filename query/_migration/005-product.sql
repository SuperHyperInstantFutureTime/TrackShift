create table Product
(
	id varchar(128) not null primary key,
	artistId varchar(128) not null,
	uploadUserId varchar(128) not null,
	title varchar(128) not null,
	titleNormalised varchar(128) not null,
	totalEarningCache decimal(16, 8) null,

	foreign key (artistId)
		references Artist (id)
		on update cascade
		on delete cascade,

	foreign key (uploadUserId)
		references User (id)
		on update cascade
		on delete cascade
);

create index Product_titleNormalised_index
    on Product (titleNormalised);
