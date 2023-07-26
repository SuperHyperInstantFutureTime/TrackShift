select
	id,
	userId,
	filePath,
	type,
	createdAt

from
	Upload

where
	userId = :userId
