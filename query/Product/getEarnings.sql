select
	Product.id as productId,
	artistId,
	Artist.name as artistName,
	title,
	round(sum(UsageOfProduct.earning), 2) as totalEarning

from
	Product

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
	UsageOfProduct.usageId = Usage.id

inner join
	Upload
on
	Upload.id = Usage.uploadId
and
	Upload.userId = ?

group by
	title

order by
	sum(earning) desc
