<?php

namespace App;

use DirectoryIterator;

class App {

	/**
	 * @param string $storagePath
	 */
	public function __construct( private string $storagePath ) {
	}

	/**
	 * Create/Update an article
	 * Better to have a  storage class to handle the locations (HardDisk/S3/GoogleDrive/...)
	 *
	 * @param string $path
	 * @param string $contents
	 *
	 * @return string
	 */
	public function save( string $path, string $contents ): string {
		file_put_contents( $path, $contents );

		return sprintf( 'Saving article %s, success!', $path );
	}

	/**
	 * Load Article Content by filename
	 *
	 * @param array $request
	 *
	 * @return string
	 */
	public function loadContent( array $request ): string {
		$filename = $request['title'] ?? null;
		$path = sprintf( '%s/%s', $this->storagePath, $filename );

		$content = '';
		if ( file_exists( $path ) ) {
			$content = file_get_contents( $path );
		}

		return $content;
	}

	/**
	 * Load articles list
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	public function loadList( array $request ): array {
		$searchPrefix = trim( $request['prefixsearch'] );

		$articles = [];
		foreach ( new DirectoryIterator( $this->storagePath ) as $file ) {
			if ( $file->isDot() || !str_contains( strtolower( $file->getFilename() ), strtolower( $searchPrefix ) ) ) {
				continue;
			}

			$articles[] = [ 'title' => $file->getFilename(), 'modifiedAt' => $file->getMTime() ];
		}

		return $articles;
	}

	/**
	 * word count have performance issue as it load the whole content of file in memory at once
	 *
	 * @return string
	 */
	public function calculateWordCount(): string {
		$wc = 0;
		$dir = new DirectoryIterator( BASE_ARTICLE_PATH );

		foreach ( $dir as $fileinfo ) {
			if ( $fileinfo->isDot() ) {
				continue;
			}
			$c = file_get_contents( BASE_ARTICLE_PATH . $fileinfo->getFilename() );
			$ch = explode( " ", $c );
			$wc += count( $ch );
		}
		return "$wc words written";
	}
}
