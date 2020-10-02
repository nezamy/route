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
namespace Just\Prototype;

use ArrayIterator;

trait Getter
{
    private $data;

    public function get(string $key, $default = '')
    {
        return $this->data[$this->getterTransformKey($key)] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$this->getterTransformKey($key)]);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function only(...$keys): array
    {
        $results = [];
        $keys = $this->variadic(...$keys);
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        return $results;
    }

    public function except(...$keys): array
    {
        $results = $this->data;
        $keys = $this->variadic(...$keys);
        foreach ($keys as $key) {
            unset($results[$key]);
        }
        return $results;
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function last()
    {
        return array_key_last($this->data);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function variadic(...$keys): array
    {
        if (is_array($keys[0])) {
            $shift = array_shift($keys);
            $keys = array_merge($shift, $keys);
        }
        return $keys;
    }

    protected function getterTransformKey($key): string
    {
        return $key;
    }
}
