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
	id = ?

limit 1
