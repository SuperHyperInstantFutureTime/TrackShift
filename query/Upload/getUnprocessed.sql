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
	usagesProcessed is null
