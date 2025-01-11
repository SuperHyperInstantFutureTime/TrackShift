select
	productId,
	earningDate,
	extractedArtistName,
	extractedProductTitle,
	sum(earning) as earningSum

from
	`Usage`

inner join
	UsageOfProduct
on
	UsageOfProduct.usageId = `Usage`.id

where
	processed is not null
and
	processedProductEarnings is null

group by
	productId, earningDate
