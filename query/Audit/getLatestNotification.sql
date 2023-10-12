select
	*

from
	Audit

where
	userId = ?
and
	isNotification = true

order by
	id desc

limit 1
