select
	id,
	artistId,
	title,
	titleNormalised

from
	Product
where
	title like '::UNSORTED_UPC::%'
