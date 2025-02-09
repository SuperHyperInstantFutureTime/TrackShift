update Product

inner join UsageOfProduct
on UsageOfProduct.productId = Product.id

inner join `Usage`
on `Usage`.id = UsageOfProduct.usageId

inner join Upload
on Upload.id = `Usage`.uploadId

set
	Upload.totalProfitCache = null

where
	Product.id = ?
