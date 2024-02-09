create table `Usage`
(
	id varchar(128) not null primary key,
	uploadId varchar(128) not null,
	data json not null,
	processed datetime null,

	foreign key (uploadId)
		references Upload(id)
		on delete cascade
		on update cascade
)
