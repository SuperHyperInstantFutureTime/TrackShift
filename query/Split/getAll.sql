select
	Split.id,
	Split.userId,
	productId

from
	Split

inner join
	Product
on
	Product.id = productId

inner join
	Artist
on
	Artist.id = Product.artistId

where
	Split.userId = ?

order by
	Artist.name, Product.title
