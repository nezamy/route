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

class GlobalRequest extends Request
{
    public function __construct()
    {
        $headers = [];
        $server = [];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
//                $key = $this->parseHeaderKey($key);
                $headers[$key] = $value;
                continue;
            }

            $key = strtolower($key);
            $server[$key] = $value;
        }

        if (! isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        if (isset($server['content_type']) && $server['content_type'] == 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $input);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
        }
        $post = array_merge($input ?? [], $_POST);

        parent::__construct($headers, $server, $_COOKIE, $_GET, $post, $_FILES);
    }
}
