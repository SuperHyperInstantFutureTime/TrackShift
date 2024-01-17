select
	id,
	artistId,
	title,
	titleNormalised

from
	Product

where
	artistId = ?

order by
	title
