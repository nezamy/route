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
namespace Just;

use Just\Http\Auth\AuthInterface;
use Just\Routing\RouteHandlerInterface;
use Just\Routing\RouteParserInterface;

/**
 * Class App.
 *
 * @method static \Just\Routing\Route any(string $uri, mixed $handler)
 * @method static \Just\Routing\Route get(string $uri, mixed $handler)
 * @method static \Just\Routing\Route head(string $uri, mixed $handler)
 * @method static \Just\Routing\Route post(string $uri, mixed $handler)
 * @method static \Just\Routing\Route put(string $uri, mixed $handler)
 * @method static \Just\Routing\Route patch(string $uri, mixed $handler)
 * @method static \Just\Routing\Route options(string $uri, mixed $handler)
 * @method static \Just\Routing\Route delete(string $uri, mixed $handler)
 * @method static \Just\Routing\Router middleware(...$middleware)
 * @method static \Just\Routing\Route auth(AuthInterface $auth, callable $callback)
 * @method static \Just\Routing\Route locale(array $locales, callable $callback)
 * @method static void addPlaceholders(array $patterns)
 * @method static void setNotfound($handler)
 * @method static void group($prefix, callable $callback)
 * @method static void use(...$middleware)
 * @method static void setHandler(RouteHandlerInterface $handler)
 * @method static void setParser(RouteParserInterface $parser)
 *
 */
class Route
{
    public static function __callStatic($method, $args)
    {
        $instance = container()->get(\Just\Routing\Router::class);
        return $instance->{$method}(...$args);
    }
}
