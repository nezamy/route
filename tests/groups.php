<?php

$route->group('/admin', function()
{
    // /admin/
    $this->get('/', function(){
        pre('welcome to admin panel');
    })->as('home');

    // /admin/settings
    $this->get('/settings', function(){
        echo 'list of settings';
    })->as('settings');

    // Nested group
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

    // Anything else
    $this->any('/*', function(){
        pre("Page ( {$this->app->request->path} ) Not Found", 6);
    });
});

$route->group('/{lang}?:isoCode2', function($lang)
{
    $default = $lang;

    if(!in_array($lang, ['ar', 'en'])){
        $default = 'en';
    }

    $this->get('/', function($lang) use($default){
        pre('Home Page');
        pre("lang in request is $lang") ;
        pre("include page_{$default}.php") ;
    })->as('home');

    $this->get('/page/{name}/', function($lang, $name)
    {
        pre(func_get_args());
        pre($this->app->request->args);
    })->as('page.name');
});

$route->group('/api', function () {

    $this->group('v1', function () {

        $this->get('/{post}', function ($target) {
            echo json_encode([
                'status' => true,
                'data' => [
                    ["{$target}" => "{$target} data here"],
                    ["{$target}" => "{$target} data here 2"]
                ],
                'message' => ''
            ]);
        })->as('post');

        // api/v1/users/add
        $this->get('/{post}/add', function ($target) {
            echo json_encode([
                'status' => true,
                'data' => [],
                'message' => "$target Created successfully"
            ]);
        })->as('post.add');

        $this->group('child', function () {

            $this->get('/', function(){
                pre('child');
            })->as('home');

            $this->group('child2', function () {
                $this->get('/', function(){
                    pre('child2');
                })->as('home');
            });

        });
    });

    $this->group(['v1', 'v2', 'v3'], function () {
//        pre($this->getCurrentGroup(), 3);
        $this->get('/{target}', function ($target) {
            echo json_encode([
                'status' => true,
                'data' => [
                    ["{$target}" => "{$target} data here"],
                    ["{$target}" => "{$target} data here 2"]
                ],
                'message' => ''
            ]);
        })->as('target');

        // api/v1/users/add
        $this->get('/{target}/add', function ($target) {
            echo json_encode([
                'status' => true,
                'data' => [],
                'message' => "$target Created successfully"
            ]);
        })->as('target.add');
        // api/v1/users/


        $this->group('child', function () {

            $this->get('/', function(){
                pre('child');
            })->as('home');

            $this->group('child2', function () {

                $this->get('/', function(){
                    pre('child2');
                })->as('home');
            });

        }, ['as' => 'testchild']);


    });
});