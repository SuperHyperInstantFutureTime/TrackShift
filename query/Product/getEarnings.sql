select
	Product.id as productId,
	artistId,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised,
	title,
	titleNormalised,

	sum(UsageOfProduct.earning) as totalEarning,

	J_Product_Cost.sumAmount as totalCost,
	J_Product_SplitPercentage.sumPercentage as percentageOutgoing

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

left join
	(
		select
			productId,
			sum(Cost.amount) as sumAmount
		from
			Cost
		where
			Cost.date >= :periodFrom and Cost.date <= :periodTo
		group by
			productId
	) J_Product_Cost
on
	J_Product_Cost.productId = Product.id

left join
	(
		select
			Split.productId,
			sum(SplitPercentage.percentage) as sumPercentage
		from
			Split
		inner join
			SplitPercentage
		on
			Split.id = SplitPercentage.splitId
		group by
			Split.productId
	) J_Product_SplitPercentage
on
	J_Product_SplitPercentage.productId = Product.id

inner join
	Artist
on
	Artist.id = Product.artistId

where
	Product.uploadUserId = :userId

group by
	Product.id

order by
	min(Product.totalEarningCache) desc

limit :limit
offset :offset
