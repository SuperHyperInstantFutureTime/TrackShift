select
	count(*) > 0 as hasUnprocessed
from
	Upload

left join
	`Usage`
on
	`Usage`.uploadId = Upload.id

where
	Upload.userId = ?
and (
	Upload.usagesProcessed is null
	or `Usage`.processedProductEarnings is null
	or `Usage`.id is null
)
