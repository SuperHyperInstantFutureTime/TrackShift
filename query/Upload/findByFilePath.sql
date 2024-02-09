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
	filePath = ?

limit 1
