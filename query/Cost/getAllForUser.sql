select distinct
	Cost.id,
	Cost.productId,
	Cost.description,
	Cost.amount

from
	Cost

inner join
	Product
on
	Product.id = Cost.productId

inner join
	Artist
on
	Artist.id = Product.artistId

inner join
	UsageOfProduct
on
	UsageOfProduct.productId = Product.id

inner join
	Usage
on
	Usage.id = UsageOfProduct.usageId

inner join
	Upload
on
	Upload.id = Usage.uploadId
and
	Upload.userId = ?

order by
	Artist.name, Product.title
