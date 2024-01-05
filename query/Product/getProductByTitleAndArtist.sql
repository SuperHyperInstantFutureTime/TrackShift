select
	id,
	artistId,
	title

from
	Product

where
	title = :title collate nocase
and
	artistId = :artistId

limit 1
