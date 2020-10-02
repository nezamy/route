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
 * ArrTrait.
 *
 * @since       1.0.0
 */
trait ArrayTrait
{
    /**
     * Get value from nested array.
     *
     * @param string $k
     * @param string $default
     *
     * @return mixed
     */
    public static function get(array $arr, $k, $default = null)
    {
        if (isset($arr[$k])) {
            return $arr[$k];
        }

        $nested = explode('.', $k);
        foreach ($nested as $part) {
            if (isset($arr[$part])) {
                $arr = $arr[$part];
                continue;
            }
            $arr = $default;
            break;
        }
        return $arr;
    }

    /**
     * set value to nested array.
     *
     * @param string $k
     * @param mixed $v
     *
     * @return array
     */
    public static function set(array $arr, $k, $v)
    {
        $nested = ! is_array($k) ? explode('.', $k) : $k;
        $count = count($nested);
        if ($count == 1) {
            return $arr[$k] = $v;
        }
        if ($count > 1) {
            $prev = '';
            $loop = 1;
            $unshift = $nested;

            foreach ($nested as $part) {
                if (isset($arr[$part]) && $count > $loop) {
                    $prev = $part;
                    array_shift($unshift);
                    ++$loop;
                    continue;
                }
                if ($loop > 1 && $loop < $count) {
                    if (! isset($arr[$prev][$part])) {
                        $arr[$prev][$part] = [];
                    }

                    $arr[$prev] = static::set($arr[$prev], $unshift, $v);
                    ++$loop;
                    break;
                }
                if ($loop >= 1 && $loop == $count) {
                    if (! is_array($arr[$prev])) {
                        $arr[$prev] = [];
                    }

                    if ($part == '') {
                        $arr[$prev][] = $v;
                    } else {
                        $arr[$prev][$part] = $v;
                    }
                } else {
                    $arr[$part] = [];
                    $prev = $part;
                    array_shift($unshift);
                    ++$loop;
                }
            }
        }
        return $arr;
    }

    /**
     * Get value if key exists or default value.
     *
     * @param string $k
     * @param string $default
     *
     * @return mixed
     */
    public static function value(array $arr, $k, $default = null)
    {
        return isset($arr[$k]) ? $arr[$k] : $default;
    }

    /**
     * Get value from string json.
     *
     * @param string $jsonStr
     * @param string $k
     * @param string $default
     *
     * @return mixed
     */
    public static function json($jsonStr, $k = null, $default = null)
    {
        $json = json_decode($jsonStr, true);
        if ($k && $json) {
            return self::get($json, $k, $default);
        }
        return $json;
    }

//    public static function replace_values($arr, $from = null, $to = "")
//    {
//        $results = [];
//        foreach ($arr as $k => $v) {
//            $toArr = (array) $v;
//            if(count($toArr) > 1 || is_array($v)){
//                $results[$k] = nullToString($toArr);
//            } else {
//                $results[$k] = $v;
//                if ($v == $from) {
//                    $results[$k] = $to;
//                }
//            }
//        }
//        return $results;
//    }
}
