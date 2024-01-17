select
	Product.id,
	Product.artistId,
	Product.title,
	Product.titleNormalised,
	Artist.name as artistName,
	Artist.nameNormalised as artistNameNormalised

from
	Product

inner join Artist
on
	Artist.id = Product.artistId

where
:__dynamicOr
