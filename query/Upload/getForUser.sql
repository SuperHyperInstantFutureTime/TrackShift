select
	id,
	userId,
	filePath,
	type

from
	Upload

where
	userId = :userId
