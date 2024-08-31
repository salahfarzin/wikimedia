<?php

session_start();

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$csrfToken = hash( 'sha256', time() );
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	$_SESSION['csrf_token'] = $csrfToken;
}

$sanitizerService = new SanitizerService();
$app = new App( new StorageService( BASE_ARTICLE_PATH ), $sanitizerService );

// article title and body, title used as name of the file to store on the disk
$title = $body = '';
// load article title and content
if ( isset( $_GET['title'] ) ) {
	$title = $sanitizerService->sanitizeFilename( $_GET['title'] );
	$body = $app->loadContent( $_GET );
}

// instead of using $_GET/$_POST, better to create or use Request class to apply some policies
// create/update articles on the disc as file
if ( $_POST ) {
	if ( !hash_equals( $_SESSION['csrf_token'], $_POST['token'] ) ) {
		return json_encode( [ 'errors' => [ 'invalid csrf token' ], 400 ] );
	}

	$app->save( $_POST['title'], $_POST['body'] );
}

// word count moved from the main thread to the api to load async and not block the main thread
// some optimization already applied, please check App::calculateWordCount docs

// views can be handled by view manager
// Security issue in the html form: No Captcha provided better to use Google recaptcha
// Security issue 2: CSP should be considered for scripts tags
require_once __DIR__ . '/../views/index.html';
