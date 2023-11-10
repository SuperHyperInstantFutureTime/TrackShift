select
	id,
	notificationCheckedAt

from
	User

where
	authwaveId = ?
