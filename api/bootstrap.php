<?php
require dirname(__DIR__)  . '/vendor/autoload.php';

set_error_handler('ErrorHandler::handleError');
set_exception_handler('ErrorHandler::handleException');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Content-type: application/json;");

$database = new Database(
    $_ENV["DB_HOST"],
    $_ENV["DB_NAME"],
    $_ENV["DB_USER"],
    $_ENV["DB_PASS"]
);

