insert into Audit (
	id,
	userId,
	isNotification,
	type,
	description,
	valueId
)
values (
	:id,
	:userId,
	false,
	'delete',
	:description,
	:valueId
)
