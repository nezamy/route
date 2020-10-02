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

class StringPrototype
{
    protected string $data;

    public function __construct(string $value)
    {
        $this->data = $value;
    }

    public function __toString(): string
    {
        return $this->data;
    }

    public function set($newValue)
    {
        $this->data = $newValue;
    }

    public function eq(string $string)
    {
        return $this->data === $string;
    }

    public function match(string $string, &$matches)
    {
        return preg_match($string, $this->data, $matches);
    }

    public function ends(string $string)
    {
        return substr($this->data, -strlen($string)) === $string;
    }

    public function startsWith(string $string)
    {
        return substr($this->data, 0, strlen($string)) === $string;
    }

    public function limit(int $limit, $trimMarker = '')
    {
        return mb_strimwidth($this->data, 0, $limit, $trimMarker);
    }

    /**
     * @return bool|int
     */
    public function contain(string $string)
    {
        return strpos($this->data, $string);
    }

    /**
     * @return $this
     */
    public function trim(string $character_mask = " \t\n\r\0\x0B")
    {
        $this->data = trim($this->data, $character_mask);
        return $this;
    }

    public function append($value): void
    {
        $this->data .= $value;
    }

    public function prepend($value): void
    {
        $this->data = $value . $this->data;
    }
}
