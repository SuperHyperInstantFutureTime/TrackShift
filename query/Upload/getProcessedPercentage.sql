SELECT
	(COUNT(*) / (SELECT COUNT(*) FROM `Usage` WHERE uploadId = :uploadId)) * 100 AS percentage
FROM
	`Usage`

WHERE
	uploadId = :uploadId
AND
	processed IS NOT NULL
