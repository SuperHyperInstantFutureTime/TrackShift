update Usage
set
	processed = true
where
	id = ?

limit 1
