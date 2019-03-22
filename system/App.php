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
     * Constructor - Define some variables.
     */
    public function __construct() {
        $this->autoload();
    }

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
     * Magic autoload.
     */
    public function autoload()
    {
        spl_autoload_register(function($className)
        {
            $className = str_replace("\\", DS, $className);
            $classNameOnly = basename($className);
            $namespace = substr($className, 0, -strlen($classNameOnly));
            if (is_file($class = BASE_PATH . "{$className}.php")) {
                return include_once($class);
            } elseif (is_file($class = BASE_PATH . strtolower($namespace). $classNameOnly . '.php')) {
                return include_once($class);
            } elseif (is_file($class = BASE_PATH . strtolower($className).'.php')) {
                return include_once($class);
            } elseif (is_file($class = BASE_PATH . $namespace . lcfirst($classNameOnly) . '.php')) {
                return include_once($class);
            }elseif (is_file($class = BASE_PATH . strtolower($namespace) . lcfirst($classNameOnly) . '.php')) {
                return include_once($class);
            }
            return false;
        });
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
