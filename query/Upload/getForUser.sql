select
	Upload.id,
	Upload.userId,
	Upload.filePath,
	Upload.type,
	coalesce(round(sum(earning), 2), 0) as totalEarning

from
	Upload

left join
	Usage
on
	Upload.id = Usage.uploadId

left join
	UsageOfProduct
on
	Usage.id = UsageOfProduct.usageId

where
	userId = :userId
