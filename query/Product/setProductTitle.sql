update Product
set
	title = :title,
	titleNormalised = :titleNormalised

where
	id = :id
