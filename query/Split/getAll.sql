select
	Split.id,
	userId,
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
	userId = ?

order by
	Artist.name, Product.title
