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
namespace Just\Support;

/**
 * Class Regex.
 * @method static string get(string $name)
 * @method static boolean has(string $name)
 * @method static void set(string $name, string $pattern)
 * @method static void update(string $name, string $pattern)
 * @method static array list()
 */
class Regex
{
    private static ?Regex $instance = null;

    private array $patterns = [
        '*' => '(.*)',
        '?' => '([^\/]+)',
        'int' => '([0-9]+)',
        'multiInt' => '([0-9,]+)',
        'title' => '([a-z_-]+)',
        'key' => '([a-z0-9_]+)',
        'multiKey' => '([a-z0-9_,]+)',
        'isoCode2' => '([a-z]{2})',
        'isoCode3' => '([a-z]{3})',
    ];

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this, '_' . $name], $arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([self::instance(), '_' . $name], $arguments);
    }

    public static function instance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function _set(string $name, string $pattern): void
    {
        if ($this->has($name)) {
            throw new \LogicException("{$name} already registered in route patterns");
        }
        $this->patterns[$name] = $pattern;
    }

    public function _get(string $name): string
    {
        return $this->patterns[$name];
    }

    public function _has(string $name): bool
    {
        return array_key_exists($name, $this->patterns);
    }

    public function _update(string $name, string $pattern): void
    {
        $this->patterns[$name] = $pattern;
    }

    public function _list(): array
    {
        return $this->patterns;
    }
}
