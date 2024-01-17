select
	id,
	name,
	userId,
	nameNormalised

from
	Artist

where
	nameNormalised = ? collate nocase
and
	userId = ?

limit 1
