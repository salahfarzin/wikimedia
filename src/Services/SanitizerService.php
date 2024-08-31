<?php

namespace App\Services;

class SanitizerService {

	/**
	 * Sanitize a filename by stripping unwanted characters and validating extension.
	 *
	 * @param string $filename
	 *
	 * @return string|null Sanitized filename or null if invalid.
	 */
	public function sanitizeFilename( string $filename ): ?string {
		// Remove any null bytes
		$filename = str_replace( "\0", '', strip_tags( $filename ) );

		// Strip any path information (protection against directory traversal)
		$filename = basename( $filename );

		// Remove the file extension, better to control extension exists in the allowed extensions list
		$extension = pathinfo( $filename, PATHINFO_EXTENSION );

		return str_replace( '.' . $extension, '', $filename );
	}

	/**
	 * Escape html chars
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function escapeHtml( string $html ): string {
		return htmlentities( $html );
	}
}
