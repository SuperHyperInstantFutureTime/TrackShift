create table UsageOfProduct
(
	id text not null constraint UsageOfProduct_pk primary key,
	usageId text not null constraint UsageOfProduct_Usage_id_fk references Usage(id) on delete cascade on update cascade,
	productId text not null constraint UsageOfProduct_Product_id_fk references Product(id) on delete cascade on update cascade,
	earning decimal(10,6) not null
)
