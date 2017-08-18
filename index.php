<?php
require 'system/startup.php';

$route = $app->route;

$route->any('/', function() {
    echo 'Hello World';
});

$route->end();
