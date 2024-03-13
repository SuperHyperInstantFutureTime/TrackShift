create table Cost (
	id varchar(128) not null primary key ,
	productId varchar(128) not null  ,
	userId varchar(128) not null,
	description text null,
	amount decimal(10,2) not null,

	foreign key (productId)
		references Product (id)
		on delete cascade
		on update cascade,

	foreign key (userId)
		references User (id)
		on delete cascade
		on update cascade
)
