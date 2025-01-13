SELECT
	(COUNT(*) / (SELECT COUNT(*) FROM TrackShift.`Usage` WHERE uploadId = :uploadId)) * 100 AS percentage
FROM
	TrackShift.`Usage`

WHERE
	uploadId = :uploadId
AND
	processed IS NOT NULL
