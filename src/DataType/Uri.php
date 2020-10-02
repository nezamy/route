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
namespace Just\DataType;

use Just\Prototype\StringPrototype;

class Uri extends StringPrototype
{
    public function getChunk(?string $uri = null)
    {
        $uri = $uri ?? $this->data;
        $chunk = $uri ? '/' . explode('/', $uri)[1] : '/';
        return $this->isStatic($chunk) ? $chunk : '/*';
    }

    public function isStatic(?string $uri = null)
    {
        $uri = $uri ?? $this->data;
        return ! $uri || $uri && strpbrk('*{}?:()', $uri) === false;
    }

    public function rtrim($char)
    {
        $this->data = rtrim($this->data, $char);
    }

    public function ltrim($char)
    {
        $this->data = ltrim($this->data, $char);
    }
}
