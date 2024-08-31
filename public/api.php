<?php

use App\App;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = new App( BASE_ARTICLE_PATH );
// TODO C: If there are performance concerns in the current code, please
// add comments on how you would fix them
// TODO D: Identify any potential security vulnerabilities in this code.
// TODO E: Document this code to make it more understandable
// for other developers.

// set the header and response code for api endpoints
// Best practices: have a version in the api url like api/v1
header( 'Content-Type: application/json' );
http_response_code( 200 );

// Load article content when the title(article filename) provided, otherwise load the article list
// it makes sense to have router to handle different endpoints and always have searchPrefix to load
// top 10/15/etc not all articles
echo json_encode( [
	'content' => !empty( $_GET['title'] ) ? $app->loadContent( $_GET ) : $app->loadList( $_GET )
] );
