select
	id,
	userId,
	filePath,
	type,
	usagesProcessed,
	totalEarningCache

from
	Upload

where
	usagesProcessed = false

order by
	id

limit 1
