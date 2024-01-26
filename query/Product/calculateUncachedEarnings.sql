select
	Product.id,
	artistId,
	title,
	titleNormalised,
	round(sum(UsageOfProduct.earning), 2) as totalEarningCache

from
	Product

inner join
	UsageOfProduct
on
	UsageOfProduct.productId = Product.id

where
	totalEarningCache is null
