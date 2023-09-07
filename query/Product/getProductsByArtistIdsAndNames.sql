select
	Product.id,
	Product.artistId,
	Product.title,
	Artist.name as artistName

from
	Product

inner join Artist
on
	Artist.id = Product.artistId

where
:__dynamicOr
