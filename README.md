# Route v2.0
Route - Fast, flexible routing for PHP, enabling you to quickly and easily build RESTful web applications.

## Installation
```bash
$ composer require nezamy/route
```
Or if you looking for ready template for using this route Go to https://github.com/nezamy/just


Route requires PHP 7.4.0 or newer.

## Usage
Only if using composer create index.php in root.

Create an index.php file with the following contents:
```php
<?php declare(strict_types=1);

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__ . DS);
//Show errors
//===================================
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
//===================================

require BASE_PATH.'vendor/autoload.php';
$request = new Just\Http\GlobalRequest;
$response = new Just\Http\Response;
$route = new Just\Routing\Router($request, $response);
container()->set(Just\Http\Request::class, $request);
container()->set(Just\Http\Response::class, $response);
container()->set(Just\Routing\Router::class, $route);

try {
    include 'app/routes.php';
    $output = $route->run();

    foreach ($output->headers->all() as $k => $v) {
        header("$k: $v");
    }
    http_response_code($output->statusCode());
    if ($output->hasRedirect()) {
        list($url, $code) = $output->getRedirect();
        header("Location: $url", true, $code);
    }

    echo $output->body();
} catch (\Error $e) {
    pre($e, 'Error', 6);
    echo response()->body();
} catch (\Exception $e) {
    pre($e, 'Exception', 6);
    echo response()->body();
}
```
app/routes.php
```php
<?php
use Just\Route;

Route::get('/', function (){
    return 'Welcome to the home page';
});

// Maybe you want to customize 404 page
Route::setNotfound(function (){
    return 'Page Not found';
});
```

## How it works
Routing is done by matching a URL pattern with a callback function.

### index.php
```php
Route::any('/', function() {
    return 'Hello World';
});

Route::post('/about', function(\Just\Http\Request $req) {
    pre($req->body, 'Request');

});

```

### The callback can be any object that is callable. So you can use a regular function:
```php
function pages() {
    return 'Page Content';
}
Route::get('/', 'pages');
```

### Or a class method:
```php
class home
{
    public function pages() {
        return 'Home page Content';
    }
}
Route::get('/', ['home', 'pages']);
// OR
$home = new home;
Route::get('/', [$home, 'pages']);
// OR
Route::get('/', 'home@pages');
```
## Method Routing
```php
Route::any('/', function() {
    // Any method requests
});

Route::get('/', function() {
    // Only GET requests
});

Route::post('/', function() {
    // Only POST requests
});

Route::put('/', function() {
    // Only PUT requests
});

Route::patch('/', function() {
    // Only PATCH requests
});

Route::delete('/', function() {
    // Only DELETE requests
});
```

## Parameters
```php
// This example will match any page name
Route::get('/{page}', function($page) {
    return "you are in $page";
});

// This example will match anything after post/
Route::get('/post/{id}', function($id) {
    // Will match anything like post/hello or post/5 ...
    // But not match /post/5/title
    return "post id $id";
});

// more than parameters
Route::get('/post/{id}/{title}', function($id, $title) {
    return "post id $id and title $title";
});

// you can get parameter in any order
Route::get('/post/{id}/{title}', function($title, $id) {
    return "post id $id and title $title";
});
```

### For “unlimited” optional parameters, you can do this:
```php
// This example will match anything after blog/ - unlimited arguments
Route::get('/blog/{any}:*', function($any) {
    pre($any);
});
```

## Regular Expressions
You can validate the args by regular expressions.
```php
// Validate args by regular expressions uses :(your pattern here)
Route::get('/{username}:([0-9a-z_.-]+)/post/{id}:([0-9]+)',
function($username, $id) {
    return "author $username post id $id";
});

// You can add named regex pattern in routes
Route::addPlaceholders([
    'username' => '([0-9a-z_.-]+)',
    'id' => '([0-9]+)'
]);

// Now you can use named regex
Route::get('/{username}:username/post/{id}:id', function($username, $id) {
    return "author $username post id $id";
});
//if the parameter name match the placeholder name just ignore placeholder and route will deduct that
Route::get('/{username}/post/{id}', function($username, $id) {
    return "author $username post id $id";
});
```

### Some named regex patterns already registered in routes
```php
[
    'int'               => '/([0-9]+)',
    'multiInt'          => '/([0-9,]+)',
    'title'             => '/([a-z_-]+)',
    'key'               => '/([a-z0-9_]+)',
    'multiKey'          => '/([a-z0-9_,]+)',
    'isoCode2'          => '/([a-z]{2})',
    'isoCode3'          => '/([a-z]{3})',
    'multiIsoCode2'     => '/([a-z,]{2,})',
    'multiIsoCode3'     => '/([a-z,]{3,})'
];
```
## Optional parameters
You can specify named parameters that are optional for matching by adding (?)
```php
Route::get('/post/{title}?:title/{date}?',
function($title, $date) {
    $content = '';
    if ($title) {
        $content = "<h1>$title</h1>";
    }else{
        $content =  "<h1>Posts List</h1>";
    }

    if ($date) {
        $content .= "<small>Published $date</small>";
    }
    return $content;

});
```
## Groups
```php
Route::group('/admin', function()
{
    // /admin/
    Route::get('/', function() {
        return 'welcome to admin panel';
    });

    // /admin/settings
    Route::get('/settings', function() {
        return 'list of settings';
    });

    // nested group
    Route::group('/users', function()
    {
        // /admin/users
        Route::get('/', function() {
            return 'list of users';
        });

        // /admin/users/add
        Route::get('/add', function() {
            return 'add new user';
        });
    });

    // Anything else
    Route::any('/{any}:*', function($any) {
        pre("Page ( $any ) Not Found", 6);
    });
});
```

### Groups with parameters
```php
Route::group('/{module}', function($lang)
{
    Route::post('/create', function() {
   
    });

    Route::put('/update', function() {
    
    });

});
```



### Middleware
```php

Route::use(function ($next){
    //validate something the call next to continue or return whatever if you want break 
    return $next();
});

```

# Full examples [here](http://nezamy.com/route)
## Support me 
https://www.paypal.me/nezamy
