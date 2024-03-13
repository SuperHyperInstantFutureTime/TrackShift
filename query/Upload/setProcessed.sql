update Upload
set
	usagesProcessed = now()

where
	id = :id
and
	userId = :userId
