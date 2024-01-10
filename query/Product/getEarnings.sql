select
	Product.id as productId,
	artistId,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised,
	title,
	titleNormalised,
	round(sum(UsageOfProduct.earning), 2) as totalEarning,
	coalesce(Cost.sumAmount, 0) as totalCost,
	coalesce(SplitPercentage.sumPercentage, 0) as percentageOutgoing

from
	Product

left join
	(
		select
			productId,
			sum(Cost.amount) as sumAmount
		from
			Cost
		group by
			productId
	) cost
on
	Cost.productId = Product.id

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
	) SplitPercentage
on
	SplitPercentage.productId = Product.id

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
