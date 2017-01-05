# Route
Route - fast, flexible route for PHP, Enables you to quickly and easily build RESTful web applications

# Usage

## How it working
Routing is done by matching a URL pattern with a callback function.
### index.php
```php
<?php
$route = $app->route;
$route->any('/', function(){
    echo 'Hello World';
});

$route->any('/about', function(){
    echo 'About';
});

$route->end();
```
