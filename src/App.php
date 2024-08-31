<?php

namespace App;

use App\Services\Exceptions\NotFoundException;
use App\Services\RecentArticlesFilter;
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

		$this->storageService->putFile( $filename, htmlspecialchars($contents) );

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
		$searchKeyword = trim( $request['search'] ?? '' );
		$fromDate = !empty( $request['from-date'] ) ? $request['fromDate'] : time();
		$basePath = $this->storageService->getBasePath();

		// fetch articles list of last certain days
		// better to adjust day limit back to last week with large data but maybe 30 days also makes sense
		$dayLimit = 365;
		$fromDate = date_create( date( 'Y-m-d', $fromDate ) )->modify( "-$dayLimit days" );

		$articles = [];
		foreach ( new RecentArticlesFilter( new DirectoryIterator( $basePath ), $fromDate ) as $file ) {
			if ( $file->isDot()
				|| !str_contains( strtolower( $file->getFilename() ), strtolower( $searchKeyword ) )
			) {
				continue;
			}

			$articles[] = [ 'title' => $file->getFilename(), 'modifiedAt' => $file->getMTime() ];
		}

		return $articles;
	}

	/**
	 * word count had performance issue by loading the whole file at one in a foreach it optimized by reading
	 * line by line and counting the word, but it can be more optimize by using Generator to avoid waste memory
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
