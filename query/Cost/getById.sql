select
	id,
	productId,
	description,
	amount,
	date

from
	Cost

where
	id = ?
-- TODO: Clamp to User ID
