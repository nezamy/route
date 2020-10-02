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

use Just\Storage\KeyValue\KeyValueStoreInterface;

trait ConvertObject
{
    public function __toString(): string
    {
        return $this->toJson();
    }

    public function toArray($data = null): array
    {
        $array = [];
        $data = $data ?? $this->all();
        foreach ((array) $data as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof StringPrototype) {
                    $array[$k] = (string) $v;
                } else {
                    $array[$k] = $v instanceof KeyValueStoreInterface ? $this->toArray($v->all()) : $v;
                }
            } else {
                $array[$k] = $v;
            }
        }

        return $array;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
