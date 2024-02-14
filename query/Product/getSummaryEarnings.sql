select
	sum(Product.totalEarningCache)

from
	Product

where
	Product.uploadUserId = :userId
