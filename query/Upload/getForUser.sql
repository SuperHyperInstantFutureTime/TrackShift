select
	Upload.id,
	Upload.userId,
	Upload.filePath,
	Upload.type,
	totalEarningCache,
	usagesProcessed

from
	Upload

where
	userId = :userId

group by Upload.id
