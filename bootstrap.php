<?php

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// I assume error_reporting controlled in php.ini for production and will not expose the errors

const BASE_ARTICLE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'articles' . DIRECTORY_SEPARATOR;

// Specify domains from which requests/methods are allowed
header('Access-Control-Allow-Origin: http://localhost:8989');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
