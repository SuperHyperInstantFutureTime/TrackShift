update Cost
set
	productId = :productId,
	description = :description,
	amount = :amount,
	date = :date

where
	id = :id
and
	userId = :userId
