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

use Just\DI\Resolver;
use LogicException;

class RouteHandler implements RouteHandlerInterface
{
    private Resolver $resolver;

    public function __construct()
    {
        $this->resolver = new Resolver();
    }

    public function call(callable $handler, array $args = [])
    {
        return $this->resolver->resolve($handler);
    }

    public function parse($handler): callable
    {
        if (is_string($handler) && ! function_exists($handler)) {
            $handler = str_replace('::', '@', $handler);
            $handler = explode('@', $handler, 2);
        }

        if (is_callable($handler)) {
            if (is_array($handler)) {
                $handler = $this->resolver->prepare($handler);
            }
            return $handler;
        }

        if (! is_callable($handler)) {
            $method = isset($handler[1]) ? '::' . $handler[1] : '';
            throw new LogicException("[{$handler[0]}{$method}] is not callable");
        }
        return $handler;
    }
}
