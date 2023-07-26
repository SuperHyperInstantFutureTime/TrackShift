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
and
	filePath = :filePath

limit 1
