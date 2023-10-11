select
	Artist.id,
	Artist.name,

	Product.id as productId,
	Product.title as productTitle

from
	Artist

inner join
	Product
on
	Product.artistId = Artist.id

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

group by
	Artist.id, Artist.name

order by
	Artist.name
