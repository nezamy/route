<?php

declare(strict_types=1);
/**
 * This file is part of Just.
 *
 * @license  https://github.com/just-framework/php/blob/master/LICENSE MIT License
 * @link     https://justframework.com/php/
 * @author   Mahmoud Elnezamy <mahmoud@nezamy.com>
 * @package  Just
 */
namespace Just\Http;

use Just\Http\Auth\AuthInterface;

/**
 * Class Auth.
 * @method bool check()
 * @method bool validate(array $credentials)
 * @method void redirectToLogin()
 * @method bool login()
 * @method void logout()
 * @method array user()
 */
class Auth
{
    private AuthInterface $auth;

    public function __construct(AuthInterface $auth)
    {
        $this->auth = $auth;
        if (! $auth->check()) {
            $auth->redirectToLogin();
        }
    }

    public function __call($method, $args)
    {
        if (method_exists($this->auth, $method)) {
            return call_user_func_array([$this->auth, $method], $args);
        }
        throw new \LogicException("{$method} Not Exists on Just\\Http\\Auth");
    }
}
