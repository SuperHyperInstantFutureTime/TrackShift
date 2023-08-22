select
	id,
	uploadId,
	data,
	processed

from
	Usage

where
	uploadId = ?
and
	processed = false
