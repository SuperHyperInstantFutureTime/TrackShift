select
	id,
	artistId,
	title,
	type

from
	Product

where
	title = :title
and
	artistId = :artistId

limit 1
