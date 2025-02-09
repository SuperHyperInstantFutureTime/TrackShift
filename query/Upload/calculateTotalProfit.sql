select
    sum(DetailedProductsSummary.totalEarning) - sum(DetailedProductsSummary.totalCost) - sum(DetailedProductsSummary.splitOutgoing) as totalProfit

from
    (
	select
		sum(UsageOfProduct.earning) as totalEarning,
		coalesce(J_Product_Cost.sumAmount, 0.0) as totalCost,
		coalesce((J_Product_SplitPercentage.sumPercentage / 100) * (sum(UsageOfProduct.earning) - J_Product_Cost.sumAmount), 0.0) as splitOutgoing

	from
		Product

	inner join
		UsageOfProduct
	on
		UsageOfProduct.productId = Product.id

	inner join
		`Usage`
	on
		`Usage`.id = UsageOfProduct.usageId

	inner join Upload
	on Upload.id = `Usage`.uploadId
	and Upload.id = ?

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

	group by
		Product.id

	order by
		sum(UsageOfProduct.earning) desc


) as DetailedProductsSummary
