select
	id,
	artistId,
	title,
	titleNormalised,
	totalEarningCache

from
	Product

where
	titleNormalised = :title collate nocase
and
	artistId = :artistId

limit 1
