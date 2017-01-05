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
### The callback can be any object that is callable. So you can use a regular function:
```php
function pages(){
    echo 'Page Content';
}
$route->get('/', 'pages');
```
### Or a class method:
```php
class home
{
    function pages(){
        echo 'Home page Content';
    }
}
$route->get('/', ['home', 'pages']);
// OR
$home = new home;
$route->get('/', [$home, 'pages']);
// OR
$route->get('/', 'home@pages');
```
## Method Routing
```php
$route->any('/', function(){
    // any method request
});

$route->get('/', function(){
    // only GET request
});

$route->post('/', function(){
    // only POST request
});

$route->put('/', function(){
    // only PUT request
});

$route->patch('/', function(){
    // only PATCH request
});

$route->delete('/', function(){
    // only DELETE request
});

// you can use multi method just add _ between method name
$route->get_post('/', function(){
    // only GET and POST request
});
```

