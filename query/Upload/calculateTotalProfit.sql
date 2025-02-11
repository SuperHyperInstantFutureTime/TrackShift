select
    sum(coalesce(DetailedProductsSummary.totalEarning, 0))
    - sum(coalesce(DetailedProductsSummary.totalCost, 0))
    - sum(coalesce(DetailedProductsSummary.splitOutgoing, 0)) as totalProfit

from
    (
	select
		sum(UsageOfProduct.earning) as totalEarning,
		J_Product_Cost.sumAmount as totalCost,
		(J_Product_SplitPercentage.sumPercentage / 100) * (sum(UsageOfProduct.earning) - J_Product_Cost.sumAmount) as splitOutgoing

	from
		Product

	left join
		UsageOfProduct
	on
		UsageOfProduct.productId = Product.id

	left join
		(
			select
				productId,
				sum(Cost.amount) as sumAmount
			from
				Cost
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
		Product.uploadId = ?

	group by
		Product.id

	order by
		sum(UsageOfProduct.earning) desc


) as DetailedProductsSummary
