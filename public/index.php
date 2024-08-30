<?php

/**
 * TODO B: Review the HTML structure and make sure that it is valid and contains
 * required elements. Edit and re-organize the HTML as needed.
 * TODO C: Review the index.php entrypoint for security and performance concerns
 * and provide fixes. Note any issues you don't have time to fix.
 * TODO D: The list of available articles is hardcoded. Add code to get a
 * dynamically generated list.
 * TODO E: Are there performance problems with the word count function? How
 * could you optimize this to perform well with large amounts of data? Code
 * comments / psuedo-code welcome.
 * TODO F (optional): Implement a unit test that operates on part of App.php
 */

use App\App;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = new App( BASE_ARTICLE_PATH );

// article title and body, title used as name of the file to store on the disk
// potential security issues (XSS and directory traversing, etc.)
$title = $body = '';
if ( isset( $_GET['title'] ) ) {
	$title = htmlentities( $_GET['title'] );
	$body = $app->loadContent( $_GET );
	$body = file_get_contents( sprintf( '%s/%s', BASE_ARTICLE_PATH, $title ) );
}

// create/update articles on the disc as file
if ( $_POST ) {
	$app->save( sprintf( "%s/%s", BASE_ARTICLE_PATH, $_POST['title'] ), $_POST['body'] );
}

// this should not be count here instead by ajax request and read from API
$wordCount = $app->calculateWordCount();

// views can be handled by view manager
require_once __DIR__ . '/../views/index.html';
