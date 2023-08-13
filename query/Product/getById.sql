select
	Product.id,
	artistId,
	title,
	Artist.name as artistName,
	type

from
	Product

inner join
	Artist
on
	Artist.id = Product.artistId

where
        Product.id = ?

limit 1