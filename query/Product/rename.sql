update Product
set
	title = :newTitle

where
	title = :oldTitle
