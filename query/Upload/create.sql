insert into Upload (
	id,
	userId,
	filePath,
	type,
	createdAt
)
values (
	:id,
	:userId,
	:filePath,
	:type,
	strftime('%s')
)
