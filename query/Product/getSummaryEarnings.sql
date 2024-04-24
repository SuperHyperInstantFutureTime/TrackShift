select
	sum(UsageOfProduct.earning) as totalEarning

from
	Product

inner join
	UsageOfProduct
on
	UsageOfProduct.productId = Product.id
and
	UsageOfProduct.earningDate >= :periodFrom
and
	UsageOfProduct.earningDate <= :periodTo

where
	Product.uploadUserId = :userId
