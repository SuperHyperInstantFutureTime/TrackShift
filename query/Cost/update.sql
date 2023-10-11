update Cost
set
	productId = :productId,
	description = :description,
	amount = :amount

where
	id = :id
