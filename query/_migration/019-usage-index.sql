create index Usage_uploadId_processed_index
    on `Usage` (uploadId, processed)
    comment 'for calculating percentage processed';
