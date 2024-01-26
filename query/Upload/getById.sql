select
	id,
	userId,
	filePath,
	type,
	totalEarningCache

from
	Upload

where
	id = :id
and
	userId = :userId

limit 1
