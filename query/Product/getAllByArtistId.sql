select
	id,
	artistId,
	title

from
	Product

where
	artistId = ?

order by
	title
