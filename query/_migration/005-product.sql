create table Product
(
	id text not null primary key,
	artistId text not null references Artist (id) on delete cascade on update cascade,
	title text not null
)
