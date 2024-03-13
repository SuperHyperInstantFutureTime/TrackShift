select
	id,
	artistId,
	uploadUserId,
	title,
	titleNormalised,
	totalEarningCache

from
	Product

where
	uploadUserId = ?

order by
	title
