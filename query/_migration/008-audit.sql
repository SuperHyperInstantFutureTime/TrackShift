create table Audit (
	id varchar(128) not null primary key ,
	userId varchar(128) not null ,
	isNotification bool not null default false,
	type enum('create', 'retrieve', 'update', 'delete') not null ,
	description text null,
	valueId varchar(128) null,
	valueField varchar(128) null,
	valueFrom varchar(128) null,
	valueTo varchar(128) null,

	foreign key (userId)
		 references User (id)
		 on delete cascade
		 on update cascade
)
