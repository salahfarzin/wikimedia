<?php

namespace App\Services;

use App\Services\Exceptions\NotFoundException;
use SplFileObject;

class StorageService {
	public function __construct( private string $basePath ) {
	}

	/**
	 * @return string
	 */
	public function getBasePath(): string {
		return $this->basePath;
	}

	/**
	 * Load file content
	 *
	 * @param string $filename
	 *
	 * @return string
	 */
	public function loadFile( string $filename ): string {
		$path = $this->basePath . $filename;
		if ( !file_exists( $path ) ) {
			throw new NotFoundException( sprintf( 'file %s not found', $path ) );
		}

		$file = new SplFileObject( $path );

		$content = '';
		while ( !$file->eof() ) {
			$content .= $file->fgets();
		}

		return $content;
	}

	/**
	 * Remove a file
	 *
	 * @param string $filename
	 *
	 * @return bool
	 */
	public function removeFile( string $filename ): bool {
		$path = $this->basePath . $filename;
		if ( !file_exists( $path ) ) {
			throw new NotFoundException( sprintf( 'file %s not found', $path ) );
		}

		return unlink( $path );
	}

	/**
	 * Store a file
	 *
	 * @param string $filename
	 * @param string $content
	 *
	 * @return int|false
	 */
	public function putFile( string $filename, string $content ): int|false {
		$path = $this->basePath . $filename;

		return file_put_contents( $path, $content );
	}

	/**
	 * Count words of a file
	 * It can be more improved by using Generator to waste less memory
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public function calcWordsCount( string $path ): int {
		$file = new SplFileObject( $this->basePath . $path );

		$wordCount = 0;
		while ( !$file->eof() ) {
			$wordCount += str_word_count( $file->fgets() );
		}

		return $wordCount;
	}
}
