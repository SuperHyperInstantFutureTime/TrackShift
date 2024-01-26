select
	sumAmount

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
	) cost
on
	Cost.productId = Product.id

where
	Product.uploadUserId = :userId

group by uploadUserId
