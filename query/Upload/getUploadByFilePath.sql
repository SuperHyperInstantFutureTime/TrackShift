select
	id,
	userId,
	filePath,
	type

from
	Upload

where
	userId = :userId
and
	filePath = :filePath

limit 1
