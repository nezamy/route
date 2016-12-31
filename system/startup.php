<?php
define('DS', DIRECTORY_SEPARATOR, true);
define('BASE_PATH', dirname(__DIR__).DS, TRUE);
ini_set('display_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "app.php";
require "function.php";

$app            = new System\App;
$app->request   = new System\Request;
$app->route     = new System\Route($app->request);
