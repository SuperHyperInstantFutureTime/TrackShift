select
	id,
	userId,
	filePath,
	type,
	totalProfitCache,
	usagesProcessed

from
	Upload

where
	filePath = ?

limit 1
