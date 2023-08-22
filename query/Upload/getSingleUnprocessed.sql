select
	id,
	userId,
	filePath,
	type,
	usagesProcessed

from
	Upload

order by
	id

limit 1
