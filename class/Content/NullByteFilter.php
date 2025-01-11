<?php
namespace SHIFT\TrackShift\Content;

use php_user_filter;

class NullByteFilter extends php_user_filter {
	public function filter($in, $out, &$consumed, $closing): int {
		while ($bucket = stream_bucket_make_writeable($in)) {
			// Remove null bytes from the bucket data
			$bucket->data = str_replace("\0", '', $bucket->data);
			$consumed += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}
}
