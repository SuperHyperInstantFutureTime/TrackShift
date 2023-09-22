select
	Upload.id,
	Upload.userId,
	Upload.filePath,
	Upload.type,
	round(coalesce(sum(UsageOfProduct.earning), 0), 2) as totalEarnings

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

group by Upload.id
