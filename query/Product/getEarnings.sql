select
	Product.id as productId,
	artistId,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised,
	title,
	titleNormalised,
	Product.totalEarningCache

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

where
	Product.uploadUserId = :userId

group by
	Product.id

order by
	min(Product.totalEarningCache) desc

limit :limit
offset :offset
