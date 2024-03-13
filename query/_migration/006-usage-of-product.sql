create table UsageOfProduct
(
	id varchar(128) not null primary key,
	usageId varchar(128) not null ,
	productId varchar(128) not null ,
	earning decimal(16,8) not null,

	foreign key (usageId)
		references `Usage`(id)
		on delete cascade
		on update cascade,

	foreign key (productId)
		references `Product`(id)
		on delete cascade
		on update cascade
)
