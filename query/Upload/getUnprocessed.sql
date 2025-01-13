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
	usagesProcessed is null
