select
	id,
	artistId,
	title,
	titleNormalised,
	totalEarningCache

from
	Product
where
	title like '::UNSORTED%'
