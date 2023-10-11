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
	true,
	'notification',
	:description,
	:valueId
)
