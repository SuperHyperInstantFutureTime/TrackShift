<?php
namespace SHIFT\TrackShift\Content;

use php_user_filter;

class NullByteFilter extends php_user_filter {
	public function filter($in, $out, &$consumed, $closing): int {
		while ($bucket = stream_bucket_make_writeable($in)) {
			$string = $bucket->data;
			// Remove null bytes
			$string = str_replace("\0", '', $string);

			// Escape tabs (\t) for JSON compatibility
			$string = preg_replace('/\t/', '\\t', $string);

			// Remove zero-width space and other invisible characters
			$string = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $string);

			// Remove other control characters, but preserve \n and \r
			$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

			// Update consumed bytes and write sanitized data to output stream
			$bucket->data = $string;
			$consumed += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}
}
