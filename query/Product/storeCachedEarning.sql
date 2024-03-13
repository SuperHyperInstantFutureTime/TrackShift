update Product
set
	totalEarningCache = :earning
where
	id = :productId
