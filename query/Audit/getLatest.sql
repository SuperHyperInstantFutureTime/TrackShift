select
	*

from
	Audit

where
	userId = ?

order by
	id desc

limit 1
