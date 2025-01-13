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
	Upload.totalEarningCache is null
