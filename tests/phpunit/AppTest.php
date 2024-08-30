<?php

namespace Tests;

use App\App;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass App
 */
class AppTest extends TestCase {

	/**
	 * @covers ::loadContent
	 */
	public function testLoadContent() {
		$app = new App( __DIR__ . '/fixtures/articles/' );

		$articleBody = $app->loadContent( [ 'title' => 'Foo' ] );
		$this->assertStringContainsString( 'Use of metasyntactic variables', $articleBody );
	}
}
