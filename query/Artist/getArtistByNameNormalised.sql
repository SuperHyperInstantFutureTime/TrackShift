select
	id,
	name,
	userId,
	nameNormalised

from
	Artist

where
	nameNormalised = ?
and
	userId = ?

limit 1
