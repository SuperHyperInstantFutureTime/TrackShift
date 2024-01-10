select
	id,
	name,
	userId,
	nameNormalised

from
	Artist

where
	id = :id
and
	userId = :userId
