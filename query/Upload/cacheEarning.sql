update Upload
set
	totalEarningCache = :earning
where
	id = :uploadId
