load data local infile :infileName
into table `Usage`
fields terminated by ','
optionally enclosed by '\"'
lines terminated by '\n'
(
	id,
	uploadId,
	data
)
