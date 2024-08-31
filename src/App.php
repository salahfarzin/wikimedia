<?php

namespace App;

use App\Services\Exceptions\NotFoundException;
use App\Services\SanitizerService;
use App\Services\StorageService;
use DirectoryIterator;

class App {

	/**
	 * @param StorageService $storageService
	 * @param SanitizerService $sanitizerService
	 */
	public function __construct(
		private readonly StorageService $storageService,
		private readonly SanitizerService $sanitizerService
	) {
	}

	/**
	 * Create/Update an article
	 * Better to have a  storage class to handle the locations (HardDisk/S3/GoogleDrive/...)
	 *
	 * @param string $filename
	 * @param string $contents
	 *
	 * @return string
	 */
	public function save( string $filename, string $contents ): string {
		$filename = $this->sanitizerService->sanitizeFilename( $filename );

		$this->storageService->putFile( $filename, $contents );

		return sprintf( 'Saving article %s, success!', $filename );
	}

	/**
	 * Load Article Content by filename
	 *
	 * @param array $request
	 *
	 * @return string
	 */
	public function loadContent( array $request ): string {
		$filename = $this->sanitizerService->sanitizeFilename( $request['title'] ?? '' );

		try {
			$content = $this->storageService->loadFile( $filename );
		} catch ( NotFoundException $exception ) {
			$content = '';
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
		$searchKeyword = trim( $request['search'] );

		$articles = [];
		foreach ( new DirectoryIterator( $this->storageService->getBasePath() ) as $file ) {
			if ( $file->isDot() || !str_contains( strtolower( $file->getFilename() ), strtolower( $searchKeyword ) ) ) {
				continue;
			}

			$articles[] = [ 'title' => $file->getFilename(), 'modifiedAt' => $file->getMTime() ];
		}

		return $articles;
	}

	/**
	 * word count have performance issue as it load the whole content of file in memory at once
	 *
	 * @return int
	 */
	public function calculateWordCount(): int {
		$wordsCount = 0;
		foreach ( new DirectoryIterator( $this->storageService->getBasePath() ) as $info ) {
			if ( $info->isDot() ) {
				continue;
			}
			$wordsCount += $this->storageService->calcWordsCount( $info->getFilename() );
		}

		return $wordsCount;
	}
}
