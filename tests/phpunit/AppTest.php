<?php

namespace Tests;

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass App
 */
class AppTest extends TestCase {

	/**
	 * @covers ::loadContent
	 */
	public function testLoadContent() {
		$app = new App( new StorageService( __DIR__ . '/fixtures/articles/' ), new SanitizerService() );

		$articleBody = $app->loadContent( [ 'title' => 'Foo' ] );
		$this->assertStringContainsString( 'Use of metasyntactic variables', $articleBody );
	}
}
