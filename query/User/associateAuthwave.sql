update User
set
	authwaveUser = :authwaveId

where
	id = :userId
