<?php

/**
 * TODO D: The list of available articles is hardcoded. Add code to get a
 * dynamically generated list.
 * TODO E: Are there performance problems with the word count function? How
 * could you optimize this to perform well with large amounts of data? Code
 * comments / psuedo-code welcome.
 * TODO F (optional): Implement a unit test that operates on part of App.php
 */

use App\App;
use App\Services\SanitizerService;
use App\Services\StorageService;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = new App( new StorageService( BASE_ARTICLE_PATH ), new SanitizerService() );

// article title and body, title used as name of the file to store on the disk
$title = $body = '';
// load article title and content
if ( isset( $_GET['title'] ) ) {
	$title = htmlentities( $_GET['title'] );
	$body = $app->loadContent( $_GET );
}

// instead of using $_GET/$_POST, better to create or use Request class to apply some policies
// create/update articles on the disc as file
if ( $_POST ) {
	$app->save( $_POST['title'], $_POST['body'] );
}

// views can be handled by view manager
require_once __DIR__ . '/../views/index.html';
