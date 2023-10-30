select
	id,
	userId,
	productId

from
	Split

where
	id = :id
and
	userId = :userId
