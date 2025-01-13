update Upload
set
	usagesProcessed = now()

where
	id = :id
