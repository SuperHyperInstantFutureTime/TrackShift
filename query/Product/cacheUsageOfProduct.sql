update UsageOfProduct
set
	productId = :toId

where
	productId = :fromId
