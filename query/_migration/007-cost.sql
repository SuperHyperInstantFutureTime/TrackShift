create table Cost (
	id text not null primary key ,
	productId text not null references Product (id) on delete cascade on update cascade ,
	description text null,
	amount decimal(10,6) not null
)
