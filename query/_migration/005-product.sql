create table Product
(
	id text not null constraint Product_pk primary key,
	artistId text not null constraint Product_Artist_id_fk references Artist (id) on delete cascade on update cascade,
	title text not null,
	type text not null
)
