select
	id,
	userId,
	filePath,
	type,
	usagesProcessed,
	totalProfitCache

from
	Upload

where
	Upload.totalProfitCache is null
