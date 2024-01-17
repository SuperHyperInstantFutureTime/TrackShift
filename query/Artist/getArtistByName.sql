select
	id,
	name,
	userId,
	nameNormalised

from
	Artist

where
	name = ? collate nocase
and
	userId = ?

limit 1
