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

trait Setter
{
    private $data;

    public function set(string $key, $value): void
    {
        $this->data[$this->setterTransformKey($key)] = $this->setterTransformValue($value);
    }

    public function push(string $key, $value): void
    {
        $key = $this->setterTransformKey($key);
        if (! isset($this->data[$key])) {
            $this->data[$key] = [];
        } elseif (! is_array($this->data[$key])) {
            $this->data[$key] = (array) $this->data[$key];
        }
        $this->data[$key][] = $this->setterTransformValue($value);
    }

    public function pop(string $key)
    {
        $key = $this->setterTransformKey($key);
        if (! isset($this->data[$key])) {
            throw new \LogicException(__CLASS__ . " | The value of [key] {$this->data[$key]} is not defined");
        }
        if (! is_array($this->data[$key])) {
            throw new \LogicException(__CLASS__ . " | The value of [key] {$this->data[$key]} is not array");
        }

        return array_pop($this->data[$key]);
    }

    public function increment(string $key, int $by = 1): int
    {
        $key = $this->setterTransformKey($key);
        if (isset($this->data[$key]) && (string) $this->data[$key] !== ((string) (int) $this->data[$key])) {
            throw new \LogicException(__CLASS__ . " | The value of [key] {$this->data[$key]} is not integer");
        }
        return $this->data[$key] = (int) ($this->data[$key] ?? 0) + $by;
    }

    public function decrement(string $key, int $by = 1): int
    {
        $key = $this->setterTransformKey($key);
        if (isset($this->data[$key]) && (string) $this->data[$key] !== ((string) (int) $this->data[$key])) {
            throw new \LogicException(__CLASS__ . " | the value of [key] {$this->data[$key]} is not integer");
        }
        return $this->data[$key] = (int) ($this->data[$key] ?? 0) - $by;
    }

    public function add(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function replace(array $data): void
    {
        $this->clear();
        foreach ($data as $k => $v) {
            $this->set((string) $k, $v);
        }
    }

    public function remove(string $key): void
    {
        unset($this->data[$this->setterTransformKey($key)]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    protected function setterTransformKey($key): string
    {
        return $key;
    }

    protected function setterTransformValue($value)
    {
        return $value;
    }
}
