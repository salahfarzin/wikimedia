<?php

namespace Tests;

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass App
 */
class AppTest extends TestCase {

	/**
	 * @covers ::loadContent
	 */
	public function testLoadContent(): void {
		$app = new App( new StorageService( __DIR__ . '/fixtures/articles/' ), new SanitizerService() );

		$articleBody = $app->loadContent( [ 'title' => 'Foo' ] );
		$this->assertStringContainsString( 'Use of metasyntactic variables', $articleBody );
	}

	/**
	 * @covers ::save
	 * @dataProvider saveDataProvider
	 *
	 * @param string $expected
	 * @param array $input
	 *
	 * @return void
	 */
	public function testSave( string $expected, array $input ): void {
		$storageService = new StorageService( '/tmp/' );
		$app = new App( $storageService, new SanitizerService() );

		$app->save( $input['title'], $input['body'] );

		$fileContent = $app->loadContent( [ 'title' => $input['filename'] ] );
		$storageService->removeFile( $input['filename'] );

		$this->assertSame( $expected, $fileContent );
	}

	/**
	 * DataProvider for testSave
	 *
	 * @see testSave
	 *
	 * @return Generator
	 */
	public function saveDataProvider(): Generator {
		yield 'Test with simple content' => [
			'expected' => 'simple text content',
			'input' => [
				'filename' => 'TEST',
				'title' => 'TEST',
				'body' => 'simple text content',
			],
		];
		yield 'Test with malicious title and content' => [
			'expected' => 'some text &lt;script&gt;window.location.redirect=&quot;http://test.com&quot;&lt;/script&gt;',
			'input' => [
				'filename' => 'TEST',
				'title' => '../../TEST.php',
				'body' => 'some text <script>window.location.redirect="http://test.com"</script>',
			],
		];
	}
}
