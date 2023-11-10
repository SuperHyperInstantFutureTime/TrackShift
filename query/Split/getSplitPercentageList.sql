select
	SplitPercentage.id,
	splitId,
	owner,
	percentage,
	contact

from
	SplitPercentage

inner join
	Split
on
	Split.id = SplitPercentage.splitId

where
	splitId = ?
and
	userId = ?
