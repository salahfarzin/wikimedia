<?php

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = new App( new StorageService( BASE_ARTICLE_PATH ), new SanitizerService() );

// Consider a Rate Limiter to prevent DDoS attacks by blocking or delaying requests from a single IP address
// It can be either adjusted on Nginx/Proxy server

// set the header and response code for api endpoints
// Best practices: have a version in the api url like api/v1 and better move response to the controller (App)
header( 'Content-Type: application/json' );
http_response_code( 200 );

// API must have an authentication endpoint
// Token must be provided by each request otherwise 401(Unauthorized) response

// Using $_GET/$_POST can cause security issues better to define or use Request class
if ( !empty( $_GET['word-count'] ) ) {
	$content = $app->calculateWordCount();
} else {
	$content = !empty( $_GET['title'] ) ? $app->loadContent( $_GET ) : $app->loadList( $_GET );
}

// Create a Response class to handle all response include json
// Load article content when the title(article filename) provided, otherwise load the article list
echo json_encode( [ 'content' => $content ] );
