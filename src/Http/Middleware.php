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

use Just\Routing\RouteHandlerInterface;

class Middleware
{
    private array $layers = [];
    private RouteHandlerInterface $handler;

    public function __construct(RouteHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function add(array $layers): void
    {
        $this->layers = array_merge($this->layers, $layers);
    }

    public function handle(callable $core)
    {
        $layers = array_reverse($this->layers);
        $final =  array_reduce($layers, function ($nextLayer, $layer) {
            return $this->createLayer($nextLayer, $layer);
        }, function () use ($core) {
            return $this->handler->call($core);
        });

        return $this->handler->call($final);
    }

    private function createLayer($nextLayer, $layer): callable
    {
        return function () use ($nextLayer, $layer) {
            container()->setVar('next', $nextLayer);
            if(is_string($layer) && class_exists($layer)){
                $layer = [$layer, 'handle'];
            }
            $layer = $this->handler->parse($layer);
            return $this->handler->call($layer);
        };
    }
}
