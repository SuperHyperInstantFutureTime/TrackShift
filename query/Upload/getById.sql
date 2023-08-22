select
	id,
	userId,
	filePath,
	type

from
	Upload

where
	id = :id
and
	userId = :userId

limit 1
