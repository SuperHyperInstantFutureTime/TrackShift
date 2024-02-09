select
	sum(earning)

from
	`UsageOfProduct`

inner join
	`Usage`
on
	`Usage`.id = `UsageOfProduct`.usageId

inner join
	`Upload`
on
	`Upload`.id = `Usage`.uploadId

where
	`Upload`.id = ?
