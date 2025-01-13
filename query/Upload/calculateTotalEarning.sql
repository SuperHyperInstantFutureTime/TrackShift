select
	sum(earning) as totalEarning

from
	Upload

inner join
	`Usage`
on
	`Usage`.uploadId = Upload.id

inner join
	UsageOfProduct
on
	UsageOfProduct.usageId = `Usage`.id

where
	Upload.id = ?

group by
	Upload.id
