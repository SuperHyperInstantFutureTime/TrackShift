select
	`Usage`.id,
	uploadId,
	data,
	processed,
	extractedArtistName,
	extractedProductTitle,
	Upload.id as uploadId,
	Upload.filePath as uploadFilePath,
	Upload.type as uploadType,
	User.id as userId

from
	`Usage`

inner join
	Upload
on
	Upload.id = `Usage`.uploadId

inner join
	User
on
	User.id = Upload.userId

where
	processed is null

limit :limit
