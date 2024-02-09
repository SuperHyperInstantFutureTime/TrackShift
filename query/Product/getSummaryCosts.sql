select
	min(J_Product_Cost.sumAmount) as totalCost

from
	Product

left join
	(
		select
			productId,
			sum(Cost.amount) as sumAmount
		from
			Cost
		group by
			productId
	) J_Product_Cost
on
	J_Product_Cost.productId = Product.id

where
	Product.uploadUserId = :userId

group by
	uploadUserId
