<?php

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;
use joshtronic\LoremIpsum;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$options = getopt( 'l::', [ 'limit::' ] );
$limit = !empty( $options['limit'] ) ? (int)$options['limit'] : 10;

$count = 0;

$app = new App( new StorageService( BASE_ARTICLE_PATH ), new SanitizerService() );

for ( $i = 0; $i < $limit; $i++ ) {
	$loremIpsum = new LoremIpsum();
	$loremIpsum->words();

	$name = $loremIpsum->word();
	$app->save( $name, $loremIpsum->paragraphs( 10 ) );

	echo sprintf( 'Creating article %s' . PHP_EOL, $name );
	++$count;
}

echo sprintf( 'generated %d articles!', $count );
