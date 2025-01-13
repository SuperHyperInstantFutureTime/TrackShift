update `Usage`
set
	data = from_base64(data)
where
	data not like '{%'
