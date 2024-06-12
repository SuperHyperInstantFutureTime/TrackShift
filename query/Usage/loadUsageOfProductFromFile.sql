load data local infile :infileName
into table `UsageOfProduct`
fields terminated by ','
optionally enclosed by '\"'
lines terminated by '\n'
(
	id,
	usageId,
	productId,
	earning,
	earningDate
)
