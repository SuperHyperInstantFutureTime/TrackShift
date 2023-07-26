replace into User (
	id,
	createdAt
)
values (
	?,
	strftime('%s')
)
