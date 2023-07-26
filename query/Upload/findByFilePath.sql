select
	id,
	userId,
	filePath,
	type,
	createdAt

from
	Upload

where
	filePath = ?

limit 1
