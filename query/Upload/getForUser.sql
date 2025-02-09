select
	Upload.id,
	Upload.userId,
	Upload.filePath,
	Upload.type,
	totalProfitCache,
# 	sum(UsageOfProduct.earning) as totalEarningCache,
	usagesProcessed

from
	Upload

# left join
# 	`Usage`
# on
# 	`Usage`.uploadId = Upload.id
#
# left join
# 	`UsageOfProduct`
# on
# 	UsageOfProduct.usageId = `Usage`.id

where
	userId = :userId

group by Upload.id
