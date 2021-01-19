# Route v2.0
Route - Fast, flexible routing for PHP, enabling you to quickly and easily build RESTful web applications.

## Installation
```bash
$ composer require nezamy/route
```
Or if you looking for ready template for using this route Go to https://github.com/nezamy/just


Route requires PHP 7.4.0 or newer.

## Changes list
- Rewrite route based on php 7.4
- Support Swoole extensions
- Support locales to build multi languages website
- Added Auth, Basic, Digest
- Availability to customize route parser and handler
- Smart dependency injection and service container


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
// let store them to container, to use them as a singleton
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

} catch (\Error $e) {
    pre($e, 'Error', 6);
} catch (\Exception $e) {
    pre($e, 'Exception', 6);
}

echo response()->body();
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

### Use with [Swoole](https://www.swoole.co.uk) 
```php
<?php
declare(strict_types=1);

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

use Just\Routing\Router;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

$http = new Server("0.0.0.0", 9501);
$http->set([
    'document_root' => '/var/www/public',
    'enable_static_handler' => true,
]);
$http->on("request", function (Request $request, Response $response) {

    $request = new Just\Http\Request(
       $request->header ?? [],
       $request->server ?? [],
       $request->cookie ?? [],
       $request->get ?? [],
       $request->post ?? [],
       $request->files ?? [],
       $request->tmpfiles ?? []
    );
    $response = new Just\Http\Response;
    $route = new Just\Routing\Router($request, $response);
	container()->set(Just\Http\Request::class, $request);
	container()->set(Just\Http\Response::class, $response);
	container()->set(Router::class, $route);
    try {
        include __DIR__ .'/app/routes.php';
        $output = $route->run();
        foreach ($output->headers->all() as $k => $v) {
            $response->header($k, $v);
        }

        $response->setStatusCode($output->statusCode());

        if ($output->hasRedirect()) {
            list($url, $code) = $output->getRedirect();
            $response->redirect($url, $code);
        }
    } catch (\Error $e) {
        pre($e, 'Error', 6);
    } catch (\Exception $e) {
        pre($e, 'Exception', 6);
    }
    $response->end(response()->body(true));
});
$http->start();
```


## How it works
Routing is done by matching a URL pattern with a callback function.


### app/routes.php
```php
Route::any('/', function() {
    return 'Hello World';
});

Route::post('/contact-us', function(\Just\Http\Request $req) {
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
Route::get('/', [home::class, 'pages']);
// OR
Route::get('/', 'home@pages');
```
## Method Routing
```php
Route::any('/', function() {});
Route::get('/', function() {});
Route::post('/', function() {});
Route::put('/', function() {});
Route::patch('/', function() {});
Route::option('/', function() {});
Route::delete('/', function() {});
```

## Parameters
```php
// This example will match any page name
Route::get('/{page}', function($page) {
    return "you are in $page";
});

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
    Route::get('/', function() {});
    // /admin/settings
    Route::get('/settings', function() {});
    // nested group
    Route::group('/users', function()
    {
        // /admin/users
        Route::get('/', function() {});
        // /admin/users/add
        Route::get('/add', function() {});
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
    Route::post('/create', function() {});
    Route::put('/update', function() {});
});
```

### Locales 
```php
// the first language is the default i.e. ar
// when you hit the site http://localhost on the first time will redirect to  http://localhost/ar
Route::locale(['ar','en'], function(){
    // will be /ar/
    Route::get('/', function($locale){
        //get current language
        pre($locale);
    });
    // /ar/contact
    Route::get('/contact', function() {});

    Route::group('/blog', function() {
        // /ar/blog/
        Route::get('/', function() {});
    });
});
// Also you can write locales like that or whatever you want
Route::locale(['ar-eg','en-us'], function(){
    // will be /ar/
    Route::get('/', function($locale){
        //get current language
        list($lang, $country) = explode('-', $locale, 2);
        pre("Lang is $lang, Country is $country");
    });
});
```
### Auth
#### Basic
```php
$auth = new \Just\Http\Auth\Basic(['users' => [
    'user1' => '123456',
    'user2' => '987654'
]]);
Route::auth($auth, function (){
    Route::get('/secret', function(\Just\Http\Request $req){
        pre("Hello {$req->user()->get('username')}, this is a secret page");
    });
});
```
#### Digest
```php
$auth = new \Just\Http\Auth\Digest(['users' => [
    'user1' => '123456',
    'user2' => '987654'
]]);
Route::auth($auth, function (){
    Route::get('/secret', function(\Just\Http\Request $req){
        pre("Hello {$req->user()->get('username')}, this is a secret page");
    });
});
```

### Middleware

#### Global
```php
Route::use(function (\Just\Http\Request $req, $next){
    //validate something the call next to continue or return whatever if you want break 
    if($req->isMobile()){
        return 'Please open from a desktop';
    }
    
    return $next();
}, function ($next){
    // another middleware
    $next();
});

// After 
Route::use(function ($next){
    $response =  $next();
    // make some action
    return $response;
});
```
#### Middleware on groups
```php
// if open from mobile device
Route::middleware(fn(\Just\Http\Request $req, $next) => !$req->isMobile() ? '' : $next())
    ->group('/mobile-only', function (){
        Route::get('/', function(\Just\Http\Request $req){
            pre($req->browser());
        });
    });
```
If you make the middleware as a class, you can pass the class with namespace.
the class should be had a `handle` method.  
```php
class MobileOnly{
    public function handle(\Just\Http\Request $req, $next){
        return !$req->isMobile() ? '' : $next();
    }
}
Route::middleware(MobileOnly::class)
    ->group('/',function (){
        Route::get('/', function(\Just\Http\Request $req){
            pre($req->browser());
        });
    });
```

#### Middleware on route
```php
Route::get('/', function(\Just\Http\Request $req){
    pre($req->browser());
})->middleware(MobileOnly::class);
```

### Dependency injection
To learn about Dependency injection and service container please visit this [link](https://github.com/nezamy/di)

### Handle and Parser customization
Example of CustomRouteHandler
```php
class CustomRouteHandler implements Just\Routing\RouteHandlerInterface
{
    public function call(callable $handler, array $args = [])
    {
        return call_user_func_array($handler, $args);
    }

    public function parse($handler): callable
    {
        if (is_string($handler) && ! function_exists($handler)) {
            $handler = explode('@', $handler, 2);
        }
        return $handler;
    }
}
\Just\Route::setHandler(new CustomRouteHandler);
```


```php
class CustomRouteParser implements RouteParserInterface
{
    public function parse(string $uri): array
    {
        $matchedParameter = [];
        $matchedPattern = [];
        $result = [];
        // parse uri here and return array of 3 elements
        // /{page}
        // /{page}?

        return ['parameters' => $matchedParameter, 'patterns' => $matchedPattern, 'result' => $result];
    }
}
\Just\Route::setParser(new CustomRouteParser);
```
