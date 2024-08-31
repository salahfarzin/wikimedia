<?php

namespace App\Services;

use DateTimeInterface;
use FilterIterator;
use Iterator;

class RecentArticlesFilter extends FilterIterator {
	private DateTimeInterface $fromDate;

	/**
	 * @param Iterator $iterator
	 * @param DateTimeInterface $fromDate
	 */
	public function __construct( Iterator $iterator, DateTimeInterface $fromDate ) {
		parent::__construct( $iterator );

		$this->fromDate = $fromDate;
	}

	/**
	 * @return bool
	 */
	public function accept(): bool {
		$fileInfo = $this->getInnerIterator()->current();
		$lastModified = $fileInfo->getMTime();

		return $lastModified >= $this->fromDate->getTimestamp();
	}
}
