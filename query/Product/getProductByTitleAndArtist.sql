select
	id,
	artistId,
	title

from
	Product

where
	title = :title
and
	artistId = :artistId

limit 1
