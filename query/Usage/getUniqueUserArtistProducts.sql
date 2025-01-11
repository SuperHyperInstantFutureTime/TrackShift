select
	Upload.userId,
	extractedArtistName,
	extractedProductTitle

from
	`Usage`

inner join
	Upload
on
	Upload.id = uploadId

where
	processed is null

group by
	Upload.userId, extractedArtistName, extractedProductTitle
