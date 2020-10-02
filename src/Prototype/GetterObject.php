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

class GetterObject
{
    use Getter;

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    private function set(string $key, $value): void
    {
        $this->data[$this->getterTransformKey($key)] = $value;
    }
}
