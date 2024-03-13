select
	Product.id,
	artistId,
	title,
	titleNormalised
	,
	sum(UsageOfProduct.earning) as totalEarningCache

from
	Product

inner join
	UsageOfProduct
on
	UsageOfProduct.productId = Product.id

inner join
	`Usage`
on
	UsageOfProduct.usageId = `Usage`.id

inner join
	Upload
on
	`Usage`.uploadId = Upload.id

where
	Product.totalEarningCache is null
and
	Upload.userId = ?

group by
	Product.id
