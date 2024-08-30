<?php

use App\App;

require_once __DIR__ . '/vendor/autoload.php';

$app = new App( BASE_ARTICLE_PATH );
// TODO A: Improve the readability of this file through refactoring and documentation.
// TODO B: Clean up the following code so that it's easier to see the different
// routes and handlers for the API, and simpler to add new ones.
// TODO C: If there are performance concerns in the current code, please
// add comments on how you would fix them
// TODO D: Identify any potential security vulnerabilities in this code.
// TODO E: Document this code to make it more understandable
// for other developers.

header( 'Content-Type: application/json' );
if ( !isset( $_GET['title'] ) && !isset( $_GET['prefixsearch'] ) ) {
	echo json_encode( [ 'content' => $app->loadList() ] );
} elseif ( isset( $_GET['prefixsearch'] ) ) {
	$list = $app->loadList();
	$ma = [];
	foreach ( $list as $ar ) {
		if ( strpos( strtolower( $ar ), strtolower( $_GET['prefixsearch'] ) ) === 0 ) {
			$ma[] = $ar;
		}
	}
	echo json_encode( [ 'content' => $ma ] );
} else {
	echo json_encode( [ 'content' => $app->loadContent( $_GET ) ] );
}
