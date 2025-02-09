update Upload
set
	Upload.totalProfitCache = null
where
	id = ?

limit 1
