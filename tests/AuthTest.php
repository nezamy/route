<?php
declare(strict_types=1);

namespace Just\Test;

use Just\DI\Container;
use Just\Http\Auth;
use Just\Http\GlobalRequest;
use Just\Http\Request;
use Just\Http\Response;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testBasicAuth() : void
    {
        Container::instance()->set(Request::class, new GlobalRequest);
        Container::instance()->set(Response::class, new Response);
        $credentials = ['users'=> ['Mahmoud'=> '123258']];
        $auth = new Auth(new Auth\Basic($credentials));
        
        $this->assertTrue($auth->validate(['username'=> 'Mahmoud', 'password' => '123258']));
        // $auth->
        $this->assertTrue(response()->headers->has('WWW-Authenticate'));
    }
    /**
     * Undocumented function
     *
     * @return void
     */
    public function testDigest(): void
    {
        Container::instance()->set(Request::class, new GlobalRequest);
        Container::instance()->set(Response::class, new Response);
        new Auth(new Auth\Digest(['users'=> ['Mahmoud'=> '123258']]));
        $this->assertTrue(response()->headers->has('WWW-Authenticate'));
    }
}
