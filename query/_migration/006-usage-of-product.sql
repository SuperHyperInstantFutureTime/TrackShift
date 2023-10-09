create table UsageOfProduct
(
	id text not null primary key,
	usageId text not null references Usage(id) on delete cascade on update cascade,
	productId text not null references Product(id) on delete cascade on update cascade,
	earning decimal(10,6) not null
)
