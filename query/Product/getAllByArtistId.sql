select
	id,
	artistId,
	title,
	titleNormalised,
	totalEarningCache

from
	Product

where
	artistId = ?

order by
	title
