select
	Product.id,
	artistId,
	title,
	titleNormalised,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised

from
	Product

inner join
	Artist
on
	Artist.id = Product.artistId

where
        Product.id = ?

limit 1
