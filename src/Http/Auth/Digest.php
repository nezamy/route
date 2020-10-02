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

class Digest implements AuthInterface
{
    public $error = '';

    protected $options = [];

    private $user = [];

    public function __construct($options)
    {
        $this->options = array_merge([
            'users' => [],
            'realm' => 'Restricted area',
            'qop' => 'auth',
            'nonce' => uniqid(),
            'error' => 'Wrong Credentials!',
            'cancel' => 'Authorization Required',
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
        $data = [];
        if (request()->server->has('PHP_AUTH_DIGEST')) {
            $data = $this->digest_parse(request()->server->get('PHP_AUTH_DIGEST'));
        } elseif (request()->headers->has('authorization')) {
            $data = $this->digest_parse(request()->headers->get('authorization'));
        }

        return $this->validate($data);
    }

    public function logout(): void
    {
        response()->headers->set('WWW-Authenticate', 'Digest realm="' . $this->options['realm'] . '",qop="' . $this->options['qop'] . '",nonce="' . $this->options['nonce'] . '",opaque="' . md5($this->options['realm']) . '"');
        response()->setStatusCode(401);
    }

    public function validate(array $credentials): bool
    {
        if (! $credentials || ! isset($this->options['users'][$credentials['username']])) {
            $this->error = $this->options['error'];
            return false;
        }
        $A1 = md5($credentials['username'] . ':' . $this->options['realm'] . ':' . $this->options['users'][$credentials['username']]);
        $A2 = md5(request()->method() . ':' . $credentials['uri']);
        $valid_response = md5($A1 . ':' . $credentials['nonce'] . ':' . $credentials['nc'] . ':' . $credentials['cnonce'] . ':' . $credentials['qop'] . ':' . $A2);
        if ($credentials['response'] != $valid_response) {
            $this->error = $this->options['error'];
            return false;
        }

        return true;
    }

    public function user(): array
    {
        return $this->user;
    }

    private function digest_parse($txt)
    {
        // protect against missing data
        $needed_parts = ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
        $data = [];
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? [] : $data;
    }
}
