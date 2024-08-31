<?php

use joshtronic\LoremIpsum;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$count = 0;
// TODO: Make the number configurable, so that one can run `php seedContent.php --limit=100`
for ( $i = 0; $i < 10; $i++ ) {
	$loremIpsum = new LoremIpsum();
	$loremIpsum->words();

	file_put_contents( sprintf( "%s/%s", BASE_ARTICLE_PATH, $loremIpsum->word() ), $loremIpsum->paragraphs( 10 ) );

	echo "Creating article\n";
	$count++;
}
echo "generated $count articles!";
