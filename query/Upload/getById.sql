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
	id = :id
and
	userId = :userId

limit 1
