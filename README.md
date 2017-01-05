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
## Parameters
```php
// this example will match any page name
$route->get('/?', function($page){
    echo "you are in $page";
});

// this example will match any thing after post/ limit 1 arg
$route->get('/post/?', function($id){
    // will match any thing like post/hello or post/5 ...
    // but not match /post/5/title
    echo "post id $page";
});

// more than parameters
$route->get('/post/?/?', function($id, $title){
    echo "post id $page and title $title";
});
```
### For “Unlimited” optional parameters, you can do this:
```php
// this example will match any thing after blog/ unlimited args
$route->get('/blog/*', function(){
    // [$this] instanceof ArrayObject so you can get all args by getArrayCopy()
    pre($this->getArrayCopy());
    pre($this[1]);
    pre($this[2]);
});
```
## Named Parameters
You can specify named parameters in your routes which will be passed along to your callback function.
```php
$route->get('/{username}/{page}', function($username, $page){
    echo "Username $username and Page $page <br>";
    // OR
    echo "Username {$this['username']} and Page {$this['page']}";
});
```

## Regular Expressions
You can validate the args by regular expressions.
```php
// validate args by regular expressions uses :(your pattern here)
$route->get('/{username}:([0-9a-z_.-]+)/post/{id}:([0-9]+)',
function($username, $id)
{
    echo "author $username post id $id";
});

// you can add named regex pattern in route
$route->addPattern([
    'username' => '/([0-9a-z_.-]+)',
    'id' => '/([0-9]+)'
]);

// now you can use named regx
$route->get('/{username}:username/post/{id}:id', function($username, $id){
    echo "author $username post id $id";
});
```
### Some named regx pattern already registered in route
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
]
```
## Optional Parameters
You can specify named parameters that are optional for matching by adding (?)
```php
$route->get('/post/{title}?:title/{date}?',
function($title, $date){
    if($title){
        echo "<h1>$title</h1>";
    }else{
        echo "<h1>Posts List</h1>";
    }

    if($date){
        echo "<small>Published $date</small>";
    }
});
```
## Groups
```php
$route->group('/admin', function()
{
    // /admin/
    $this->get('/', function(){
        echo 'welcome to admin panel';
    });

    // /admin/settings
    $this->get('/settings', function(){
        echo 'list of settings';
    });

    // nested group
    $this->group('/users', function()
    {
        // /admin/users
        $this->get('/', function(){
            echo 'list of users';
        });

        // /admin/users/add
        $this->get('/add', function(){
            echo 'add new user';
        });
    });

    // anything else
    $this->any('/*', function(){
        pre("Page ( {$this->app->request->path} ) Not Found", 6);
    });
});
```

### Groups with parameters
```php
$route->group('/{lang}?:isoCode2', function($lang)
{
    $default = $lang;

    if(!in_array($lang, ['ar', 'en'])){
        $default = 'en';
    }

    $this->get('/', function($lang) use($default){
        echo "lang in request is $lang<br>";
        echo "include page_{$default}.php";
    });

    $this->get('/page/{name}/', function($lang, $name)
    {
        pre(func_get_args());
        pre($this->app->request->args);
    });
});
```

# Full examples will be available soon on http://nezamy.com/route
