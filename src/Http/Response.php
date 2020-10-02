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

use Just\Prototype\ObjectStore;

class Response
{
    public $headers;

    protected $_body = '';

    protected $_statusCode = 200;

    protected $_redirect = [];

    private $_end = false;

    public function __construct()
    {
        $this->headers = new ObjectStore();
    }

    public function body($clean = false): string
    {
        $body = $this->_body;
        if ($clean) {
            $this->_body = '';
        }
        return $body;
    }

    public function write($body): void
    {
        if (! $this->_end) {
            $this->_body .= $body;
        }
    }

    public function end(string $body = ''): void
    {
        if (! $this->_end) {
            $this->_body = $body;
            $this->_end = true;
        }
    }

    public function isEnded(): bool
    {
        return $this->_end;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->_statusCode = $statusCode;
    }

    public function statusCode(): int
    {
        return $this->_statusCode;
    }

    public function redirectTo(string $location, int $status_code = 302)
    {
        $this->_redirect = [$location, $status_code];
    }

    public function hasRedirect(): bool
    {
        return count($this->_redirect) > 0;
    }

    public function getRedirect()
    {
        return $this->_redirect;
    }
}
