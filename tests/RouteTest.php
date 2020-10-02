<?php
declare(strict_types=1);

namespace Just\Test;

use DummyRequest;
use Just\DataType\Uri;
use Just\Http\GlobalRequest;
use Just\Http\Response;
use Just\Routing\Route;
use Just\Support\Regex;
use PHPUnit\Framework\TestCase;
use Just\Routing\Router;
class controller{
    public static function method(){
        pre('Methods', 'Controller');
    }
}

class RouteTest extends TestCase
{
    protected function app(): Router{
        return new Router(new GlobalRequest(), new Response());
    }


    public function testShortcuts() : void
    {
        $r = $this->app();
        $r->add('GET', '/get/users', 'Just\Test\controller::method');
        $r->delete('/delete', 'Just\Test\controller::method');
        $r->get('/get', 'Just\Test\controller::method');
        $r->head('/head', 'Just\Test\controller::method');
        $r->patch('/patch', 'Just\Test\controller::method');
        $r->post('/post', 'Just\Test\controller::method');
        $r->put('/put', 'Just\Test\controller::method');
        $r->options('/options', 'Just\Test\controller::method');

        $expected = [
            '/get' => [
                new Route('GET', new Uri('/get/users'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/get'), 'Just\Test\controller::method'),
            ],
            '/delete' => [
                new Route('DELETE', new Uri('/delete'), 'Just\Test\controller::method')
            ],
            '/head' => [
                new Route('HEAD', new Uri('/head'), 'Just\Test\controller::method')
            ],
            '/patch' => [
                new Route('PATCH', new Uri('/patch'), 'Just\Test\controller::method')
            ],
            '/post' => [
                new Route('POST', new Uri('/post'), 'Just\Test\controller::method')
            ],
            '/put' => [
                new Route('PUT', new Uri('/put'), 'Just\Test\controller::method')
            ],
            '/options' => [
                new Route('OPTIONS', new Uri('/options'), 'Just\Test\controller::method')
            ]
        ];
        self::assertEquals($expected, $r->export());
    }

    public function testGroups() : void
    {
        $r = $this->app();
        $r->get('/get', 'Just\Test\controller::method');
        $r->group('/api/v1', function (Router $r) {
            $r->get('/posts', 'Just\Test\controller::method');
            $r->post('/posts/create', 'Just\Test\controller::method');

            $r->group('/nested-group', function (Router $r) {
                $r->get('/posts', 'Just\Test\controller::method');
                $r->post('/posts/create', 'Just\Test\controller::method');
            });
        });

        $r->group('/admin', static function (Router $r): void {
            $r->get('-some-info', 'Just\Test\controller::method');
        });
        $r->group('/admin-', static function (Router $r): void {
            $r->get('more-info', 'Just\Test\controller::method');
        });

        $expected = [
            '/get' => [
                new Route('GET', new Uri('/get'), 'Just\Test\controller::method'),
            ],
            '/api' => [
                new Route('GET', new Uri('/api/v1/posts'), 'Just\Test\controller::method'),
                new Route('POST', new Uri('/api/v1/posts/create'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/api/v1/nested-group/posts'), 'Just\Test\controller::method'),
                new Route('POST', new Uri('/api/v1/nested-group/posts/create'), 'Just\Test\controller::method'),
            ],
            '/admin-some-info' => [
                new Route('GET', new Uri('/admin-some-info'), 'Just\Test\controller::method'),
            ],
            '/admin-more-info' => [
                new Route('GET', new Uri('/admin-more-info'), 'Just\Test\controller::method'),
            ]
        ];

        self::assertEquals($expected, $r->export());
    }

    public function testRouteDynamic() : void
    {
        $r = $this->app();
        $r->get('/{user}/{id}?', 'Just\Test\controller::method');
        $r->get('/user/{user}/{id}?', 'Just\Test\controller::method');
        self::assertEquals([
            '/*' => [
                new Route('GET', new Uri('/{user}/{id}?'), 'Just\Test\controller::method')
            ],
            '/user' => [
                new Route('GET', new Uri('/user/{user}/{id}?'), 'Just\Test\controller::method')
            ],
        ], $r->export());
        try {
            $r->match('GET', '/username');
        } catch (\LogicException $e) {
        }
        $expect = new Route('GET', new Uri('/{user}/{id}?'), 'Just\Test\controller::method');
        $expect->setArgs([
            'user' => 'username',
            'id' => null
        ]);
        self::assertEquals($expect, $r->getMatched());

        try {
            $r->match('GET', '/username/10/');
        } catch (\LogicException $e) {
        }

        $expect = new Route('GET', new Uri('/{user}/{id}?'), 'Just\Test\controller::method');
        $expect->setArgs([
            'user' => 'username',
            'id' => 10
        ]);
        self::assertEquals($expect, $r->getMatched());
    }

    public function testGroupDynamic() : void
    {
        $r = $this->app();
        $r->group('/{lang}:isoCode2', function (Router $r) {
            $r->get('/{page}', 'Just\Test\controller::method');
            $r->get('/post/{post}', 'Just\Test\controller::method');
        });

        self::assertEquals([
            '/*' => [
                new Route('GET', new Uri('/{lang}:isoCode2/{page}'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/{lang}:isoCode2/post/{post}'), 'Just\Test\controller::method')
            ]
        ], $r->export());
        try {
            $r->match('GET', '/ar/page');
        } catch (\LogicException $e) {
        }

        $expect = new Route('GET', new Uri('/{lang}:isoCode2/{page}'), 'Just\Test\controller::method');
        $expect->setArgs([
            'lang' => 'ar',
            'page' => 'page'
        ]);
        self::assertEquals($expect, $r->getMatched());
    }

    public function testGroupDynamicOptionalParameter() : void
    {
        $r = $this->app();
        $r->group('/{lang}?:isoCode2', function (Router $r) {
            $r->get('/{page}', 'Just\Test\controller::method');
            $r->get('/post/{post}', 'Just\Test\controller::method');
        });

        self::assertEquals([
            '/*' => [
                new Route('GET', new Uri('/{lang}?:isoCode2/{page}'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/{lang}?:isoCode2/post/{post}'), 'Just\Test\controller::method')
            ]
        ], $r->export());

        try {
            $r->match('GET', '/page');
        } catch (\LogicException $e) {
        }

        $expect = new Route('GET', new Uri('/{lang}?:isoCode2/{page}'), 'Just\Test\controller::method');
        $expect->setArgs([
            'lang' => '',
            'page' => 'page'
        ]);
        self::assertEquals($expect, $r->getMatched());

        try {
            $r->match('GET', '/ar/page');
        } catch (\LogicException $e) {
        }

        $expect = new Route('GET', new Uri('/{lang}?:isoCode2/{page}'), 'Just\Test\controller::method');
        $expect->setArgs([
            'lang' => 'ar',
            'page' => 'page'
        ]);
        self::assertEquals($expect, $r->getMatched());
    }

    public function testAddPlaceholder() : void
    {
        $r = $this->app();
        $r->addPlaceholders([
           'test' => '(test_[a-z_-]+)'
        ]);
        $this->assertSame(Regex::get('test'), '(test_[a-z_-]+)');
    }

    public function testLocale(): void
    {
        $r = $this->app();
        $r->locale(['ar', 'en'], function (Router $r) {
            $r->get('/', 'Just\Test\controller::method');
            $r->get('/page', 'Just\Test\controller::method');
            $r->get('/page/{page}', 'Just\Test\controller::method');
        });
        self::assertEquals([
            '/*' => [
                new Route('GET', new Uri('/{@locale}?:(ar|en)/'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/{@locale}?:(ar|en)/page'), 'Just\Test\controller::method'),
                new Route('GET', new Uri('/{@locale}?:(ar|en)/page/{page}'), 'Just\Test\controller::method'),
            ]
        ], $r->export());
        try {
            $r->match('GET', '/en/page');
        } catch (\LogicException $e) {
        }
        $expect = new Route('GET', new Uri('/{@locale}?:(ar|en)/page'), 'Just\Test\controller::method');
        $expect->setArgs([
            'locale' => 'en',
        ]);
        self::assertEquals($expect, $r->getMatched());
    }

    public function testLocaleRedirect(): void
    {
        $req = new DummyRequest();
        $req->setUri('/page');
        $r = new Router($req, new Response());
        $r->locale(['ar', 'en'], function (Router $r) {
            $r->get('/', function () {
            });
            $r->get('/page', function () {
            });
        });

        $match = $r->run();
        $this->assertTrue($match->hasRedirect());
        $this->assertTrue($match->isEnded());
        $this->assertEquals(['/ar/page', 302], $match->getRedirect());
    }
}
