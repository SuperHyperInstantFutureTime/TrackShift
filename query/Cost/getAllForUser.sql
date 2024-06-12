select distinct
	Cost.id,
	Cost.productId,
	Cost.userId,
	Cost.description,
	Cost.amount,
	Cost.date,
	Artist.name,
	Product.title

from
	Cost

inner join
	Product
on
	Product.id = Cost.productId

inner join
	Artist
on
	Artist.id = Product.artistId

where
	Cost.userId = ?

order by
	Artist.name, Product.title
