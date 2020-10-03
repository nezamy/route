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
namespace Just\Routing;

use Just\DataType\Uri;
use Just\Http\Auth\AuthInterface;
use Just\Prototype\ArrayPrototype;

class Route
{
    public ArrayPrototype $options;

    private string $method;

    private Uri $uri;

    /**
     * @var callable
     */
    private $handler;

    private array $args = [];

    private array $middleware = [];

    public function __construct(string $method, Uri $uri, $handler, array $options = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->handler = $handler;
        $this->options = new ArrayPrototype($options);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): Uri
    {
        return $this->uri;
    }

//    parse before get

    public function getHandler(RouteHandlerInterface $handler): callable
    {
        return $handler->parse($this->handler);
    }

    public function addNamespaceToHandler()
    {
        if (is_string($this->handler)) {
            $this->handler = $this->options->get('namespace') . '\\' . $this->handler;
        } elseif (is_array($this->handler)) {
            $this->handler[0] = $this->options->get('namespace') . '\\' . $this->handler[0];
        }
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function middleware(...$middleware)
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function hasMiddleware()
    {
        return count($this->middleware) > 0;
    }

    public function withoutMiddleware(...$middleware)
    {
        $this->middleware = array_filter($this->middleware, fn ($var) => ! in_array($var, $middleware));
        return $this;
    }
}
