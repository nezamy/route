<?php
/**
 * Just Framework - It's a PHP micro-framework for Full Stack Web Developer
 *
 * @package     Just Framework
 * @copyright   2016 (c) Mahmoud Elnezamy
 * @author      Mahmoud Elnezamy <http://nezamy.com>
 * @link        http://justframework.com
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @version     1.0.0
 */
namespace System;
/**
 * App
 *
 * @package     Just Framework
 * @author      Mahmoud Elnezamy <http://nezamy.com>
 * @since       1.0.0
 */
class App
{
    private static $instance;
    
    /**
     * Singleton instance.
     *
     * @return $this
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Magic call.
     *
     * @param string   $method
     * @param array    $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return  isset($this->{$method}) && is_callable($this->{$method})
            ? call_user_func_array($this->{$method}, $args) : null;
    }

    /**
     * Set new variables and functions to this class.
     *
     * @param string      $k
     * @param mixed    $v
     */
    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
    }
}
