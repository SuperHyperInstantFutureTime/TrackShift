select
	id,
	productId,
	description,
	amount

from
	Cost

where
	id = ?
-- TODO: Clamp to User ID
