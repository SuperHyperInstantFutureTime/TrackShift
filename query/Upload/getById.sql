select
	id,
	userId,
	filePath,
	type,
	totalEarningCache,
	usagesProcessed

from
	Upload

where
	id = ?

limit 1
