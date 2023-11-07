select
	id,
	name,
	userId

from
	Artist

where
	id = :id
and
	userId = :userId
