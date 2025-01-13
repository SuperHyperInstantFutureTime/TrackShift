create table ProductEarning (
	id varchar(128) primary key ,
	productId varchar(128) not null,
	earning decimal(16,8),
	date date,

	foreign key (`productId`)
		references Product(`id`)
		on delete cascade
		on update cascade
)
