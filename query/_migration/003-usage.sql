create table Usage
(
	id text not null primary key,
	uploadId text not null references Upload (id) on update cascade on delete cascade,
	data text not null,
	processed bool not null default false
)
