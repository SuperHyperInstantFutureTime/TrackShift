select
	id,
	userId,
	filePath,
	type,
	createdAt

from
	Upload

where
	id = :id
and
	userId = :userId

limit 1
