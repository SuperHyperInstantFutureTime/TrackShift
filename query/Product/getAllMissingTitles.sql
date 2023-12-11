select
	id,
	artistId,
	title

from
	Product
where
	title like '::UPC::%'
