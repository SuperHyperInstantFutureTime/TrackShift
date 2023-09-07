create table Usage
(
	id text not null constraint Usage_pk primary key,
	uploadId text not null constraint Usage_Upload_id_fk references Upload (id) on update cascade on delete cascade,
	data text not null,
	processed bool not null default false
)
