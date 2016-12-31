<?php
require 'system/startup.php';

$route = $app->route;
$route->any('/', 'App\test@index')->as('home');
// $route->any('/', ['App\test', 'index'])->as('home2');

$route->any('/about', function(){
    pre('home');
})->as('about');

$route->group('/admin', function()
{
    $this->any('/', function(){
        pre('dashboard');
    })->as('admin.dash');

    $this->get('user', function(){
        pre('user');
    })->as('admin.user');
});

//
// $route->get('/admin/{one}?:([0-9]+)/{two}?:title', function($w){
//     pre($this);
//     // pre($this['two']);
//     // pre($w);
// });

$route->any('/admin/*', function(){
    pre('admin error');
});

$route->end();
