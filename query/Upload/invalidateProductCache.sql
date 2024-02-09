update Product

inner join
	UsageOfProduct
on
	Product.id = UsageOfProduct.productId

inner join
	`Usage`
on
	UsageOfProduct.usageId = `Usage`.id

inner join
	Upload
on
	`Usage`.uploadId = Upload.id

set
	Product.totalEarningCache = null

where
	Upload.id = ?
