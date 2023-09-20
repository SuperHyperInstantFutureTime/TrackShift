create table Cost (
	id text not null constraint  Cost_pk primary key ,
	productId text not null constraint Cost_Product_id_fk references Product (id) on delete cascade on update cascade ,
	description text null,
	amount decimal(10,6) not null
)
