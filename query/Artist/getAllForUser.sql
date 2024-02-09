select
	id,
	name,
	userId,
	nameNormalised

from
	Artist

where
	userId = ?

order by
	name
