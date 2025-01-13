select
	`Usage`.id,
	productId,
	earning,
	earningDate,
	extractedArtistName,
	extractedProductTitle

from
	`Usage`

left join
	UsageOfProduct
on
	UsageOfProduct.usageId = `Usage`.id

where
	`Usage`.processed is not null
and
	`Usage`.processedProductEarnings is null
