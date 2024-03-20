select
	id,
	notificationCheckedAt

from
	User

where
	authwaveUser = ?
