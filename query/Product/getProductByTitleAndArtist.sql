select
	id,
	artistId,
	title,
	titleNormalised,
	totalEarningCache

from
	Product

where
	title = :title collate nocase
and
	artistId = :artistId

limit 1
