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

use Just\DI\Resolver;

class Middleware
{
    private array $layers = [];

    public function add(array $layers): void
    {
        $this->layers = array_merge($this->layers, $layers);
    }

    public function handle(callable $core)
    {
        $layers = array_reverse($this->layers);
        return array_reduce($layers, function ($nextLayer, $layer) {
            return $this->createLayer($nextLayer, $layer);
        }, function () use ($core) {
            return (new Resolver())->resolve($core);
        });
    }

    private function createLayer(callable $nextLayer, callable $layer): callable
    {
        return function () use ($nextLayer, $layer) {
            container()->setVar('next', $nextLayer);
            if (is_object($layer) && method_exists($layer, 'handle')) {
                return (new Resolver())->resolve([$layer, 'handle']);
            }
            return (new Resolver())->resolve($layer);
        };
    }
}
