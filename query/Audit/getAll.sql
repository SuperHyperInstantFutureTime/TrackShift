select
	id,
	userId,
	isNotification,
	type,
	description,
	valueId,
	valueField,
	valueFrom,
	valueTo

from
	Audit

where
	userId = ?

order by
	id desc
