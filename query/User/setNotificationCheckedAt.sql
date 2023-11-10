update User
set
	notificationCheckedAt = :checkedAt

where
	id = :userId
