<?php
define('DS', DIRECTORY_SEPARATOR, true);
define('BASE_PATH', dirname(__DIR__) . DS, TRUE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
require "../system/App.php";
$app            = System\App::instance();
$app->request   = System\Request::instance();
$app->route     = System\Route::instance($app->request);

$route = $app->route;

include 'before.php';
include 'groups.php';
include 'resource.php';



$route->end();