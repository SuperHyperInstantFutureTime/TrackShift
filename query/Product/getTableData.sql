select
	Product.id as productId,
	Product.artistId,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised,
	Product.title,
	Product.titleNormalised,

	sum(UsageOfProduct.earning) as totalEarning,
	J_Product_Cost.sumAmount as totalCost,
	(J_Product_SplitPercentage.sumPercentage / 100) * (sum(UsageOfProduct.earning) - J_Product_Cost.sumAmount) as splitOutgoing,

	(coalesce(sum(UsageOfProduct.earning), 0) - J_Product_Cost.sumAmount - coalesce((J_Product_SplitPercentage.sumPercentage / 100) * (sum(UsageOfProduct.earning) - J_Product_Cost.sumAmount), 0)) as profit

from
	Product

left join
	UsageOfProduct
on
	UsageOfProduct.productId = Product.id
and
	(UsageOfProduct.earningDate between :periodFrom and :periodTo)

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

left join
	Artist
on
	Artist.id = Product.artistId

where
	Product.uploadUserId = :userId

group by
	Product.id,
	Product.titleNormalised

order by
	profit desc,
	Product.titleNormalised

limit :limit
offset :offset
