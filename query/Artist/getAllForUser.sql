select
	Artist.id,
	Artist.name,
	Artist.userId

from
	Artist

where
	userId = ?

order by
	Artist.name
