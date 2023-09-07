select
	id,
	userId,
	filePath,
	type,
	usagesProcessed

from
	Upload

where
	usagesProcessed = false

order by
	id

limit 1
