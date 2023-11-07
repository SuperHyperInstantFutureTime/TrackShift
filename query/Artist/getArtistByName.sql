select
	id,
	name,
	userId

from
	Artist

where
	name = ?
and
	userId = ?

limit 1
