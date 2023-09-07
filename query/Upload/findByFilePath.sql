select
	id,
	userId,
	filePath,
	type

from
	Upload

where
	filePath = ?

limit 1
