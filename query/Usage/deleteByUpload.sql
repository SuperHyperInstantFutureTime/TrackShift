delete from UsageOfProduct
where
    usageId in (select usageId from Usage where Usage.uploadId = :uploadId);

delete from Usage
where
	Usage.uploadId = :uploadId
