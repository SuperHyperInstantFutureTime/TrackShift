select
	sum(Cost.amount) as totalCost

from
	Product

left join
	Cost
on
	Cost.productId = Product.id

where
	Product.uploadUserId = :userId
