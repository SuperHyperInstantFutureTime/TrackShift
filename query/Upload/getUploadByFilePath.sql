select
	id,
	userId,
	filePath,
	type,
	totalEarningCache

from
	Upload

where
	userId = :userId
and
	filePath = :filePath

limit 1
