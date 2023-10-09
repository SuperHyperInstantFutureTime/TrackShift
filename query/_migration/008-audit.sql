create table Audit (
	id text not null primary key ,
	userId text not null references User (id) on delete cascade on update cascade ,
	isNotification bool not null default false,
	type text not null , -- created, updated, deleted
	description text null,
	valueId text null,
	valueField text null,
	valueFrom text null,
	valueTo text null
)
