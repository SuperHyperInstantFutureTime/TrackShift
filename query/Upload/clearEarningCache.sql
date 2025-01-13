update Upload
set
	totalEarningCache = null
where
	id = ?

limit 1
