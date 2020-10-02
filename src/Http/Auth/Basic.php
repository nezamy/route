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
namespace Just\Http\Auth;

class Basic implements AuthInterface
{
    public $error = '';

    protected $options = [];

    private $user = [];

    public function __construct($options)
    {
        $this->options = array_merge([
            'users' => [],
            'realm' => 'Restricted area',
        ], $options);
    }

    public function check(): bool
    {
        return $this->login();
    }

    public function redirectToLogin()
    {
        $this->logout();
        if (! $this->login()) {
            $this->logout();
            response()->end($this->error);
        }
    }

    public function login(array $credentials = []): bool
    {
        $username = $password = null;

        if (request()->server->has('PHP_AUTH_USER') && request()->server->has('PHP_AUTH_PW')) {
            $username = request()->server->get('PHP_AUTH_USER');
            $password = request()->server->get('PHP_AUTH_PW');
        } elseif (request()->headers->has('authorization')) {
            $auth = request()->headers->get('authorization');
            if (strpos(strtolower($auth), 'basic') === 0) {
                [$username, $password] = explode(':', base64_decode(substr($auth, 6)));
            }
        }

        return $this->validate(['username' => $username, 'password' => $password]);
    }

    public function logout(): void
    {
        response()->headers->set('WWW-Authenticate', 'Basic realm="' . $this->options['realm'] . '"');
        response()->setStatusCode(401);
    }

    public function validate(array $credentials): bool
    {
        if ($credentials['username'] && $credentials['password']) {
            $username = filter_var($credentials['username'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
            $password = filter_var($credentials['password'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW);
            $users = $this->options['users'];
            if (isset($users[$username]) && $users[$username] == $password) {
                unset($credentials['password']);
                $this->user = $credentials;
                return true;
            }
        }
        $this->error = 'Authorization Required';
        return false;
    }

    public function user(): array
    {
        return $this->user;
    }
}
