select
	Artist.id,
	Artist.name,
	Artist.userId,
	Artist.nameNormalised

from
	Artist

where
	userId = ?

order by
	Artist.name
