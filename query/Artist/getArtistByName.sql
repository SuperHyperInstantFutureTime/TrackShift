select
	id,
	name,
	userId

from
	Artist

where
	name = ? collate nocase
and
	userId = ?

limit 1
