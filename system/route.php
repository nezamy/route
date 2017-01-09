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
 * Route
 *
 * @package     Just Framework
 * @author      Mahmoud Elnezamy <http://nezamy.com>
 * @since       1.0.0
 */
class Route
{
    private static $instance;
    /**
     * Named parameters list.
     */
    protected $pattern      = [
        '/*'                => '/(.*)',
        '/?'                => '/([^\/]+)',
        'int'               => '/([0-9]+)',
        'multiInt'          => '/([0-9,]+)',
        'title'             => '/([a-z_-]+)',
        'key'               => '/([a-z0-9_]+)',
        'multiKey'          => '/([a-z0-9_,]+)',
        'isoCode2'          => '/([a-z]{2})',
        'isoCode3'          => '/([a-z]{3})',
        'multiIsoCode2'     => '/([a-z,]{2,})',
        'multiIsoCode3'     => '/([a-z,]{3,})'
    ];

    /**
     * Constructor - Define some variables.
     */
    public function __construct(Request $req)
    {
        $this->routes         = $this->urls = [];
        $this->group          = $this->matchedPath = '';
        $this->matched        = false;
        $this->pramsGroup     = $this->matchedArgs = $this->pattGroup = [];
        $this->groupAs        = $this->fullArg = '';
        $this->isGroup        = false;
        $this->req            = $req;
        $this->roles          = [];
        $this->bindedGroups   = $this->currentGroup = [];
        defined('URL') || define('URL', $req->url, TRUE);

    }

    /**
     * Singleton instance.
     *
     * @return $this
     */
    public static function instance(Request $req)
    {
        if (null === static::$instance) {
            static::$instance = new static($req);
        }
        return static::$instance;
    }

    /**
     * Register a route with callback.
     *
     * @param array           $method
     * @param string|array    $uri
     * @param callable        $callback
     * @param array           $options
     *
     * @return $this
     */
    public function route (array $method, $uri, $callback, $options = [])
    {
        if ( is_array($uri) ) {
            foreach ( $uri as $u ) {
                $this->route($method, $u, $callback, $options);

            }
            return $this;
        }

        $options = array_merge(['ajaxOnly' => false, 'continu' => false], $options);

        if($uri != '/'){
            $uri = $this->removeDuplSlash($uri).'/';
        }

        // Replace named uri param to regex pattern.
        $pattern  = $this->namedParameters($uri);

        // Clean uri for route name.
        // $this->currentUri = trim( str_replace($this->patt, '', $pattern) ,'/').'/';
        $this->currentUri = $pattern;

        if ($options['ajaxOnly'] == false || $options['ajaxOnly'] && $this->req->ajax)
        {
            // If matched before, skip this.
            if ($this->matched === false)
            {
                // Prepare.
                $pattern = $this->prepare(
                    str_replace(['/?', '/*'], [ $this->pattern['/?'], $this->pattern['/*'] ], $this->removeDuplSlash($this->group.$pattern))
                );

                // If matched.
                $method = count($method) > 0 ? in_array($this->req->method, $method) : true;
                if ( $method && $this->matched($pattern) )
                {
                    if($this->isGroup){
                        $this->prams = array_merge($this->pramsGroup, $this->prams);
                    }

                    $this->req->args = $this->bindArgs($this->prams, $this->matchedArgs);

                    $this->matchedPath   = $this->currentUri;
                    $this->routeCallback = $callback;
                    $this->saveit[]      = $this->getGroupAs().$this->matchedPath;

                    if($options['continu']){
                        $this->callback($this->routeCallback, $this->req->args);
                        $this->routeCallback = $this->matched = false;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Group of routes.
     *
     * @param string|array    $group
     * @param callable        $callback
     *
     * @return $this
     */
    public function group($group, callable $callback) {
        if ( is_array($group) ) {
            foreach ( $group as $p ) {
                $this->group($p, $callback);
            }
            return $this;
        }

        $group = $this->removeDuplSlash($group.'/');
        $group = $this->namedParameters($group, true);

        $this->matched( $this->prepare($group, false), false );

        $this->currentGroup = $group;
        // Add this group and sub-groups to append to route uri.
        $this->group     .= $group;
        // Bind to Route Class.
        $callback         = $callback->bindTo($this);
        // Call with args.
        call_user_func_array($callback, $this->bindArgs($this->pramsGroup, $this->matchedArgs));

        $this->isGroup     = false;
        $this->pramsGroup  = $this->pattGroup = [];
        $this->group = substr($this->group, 0, -strlen($group));

        return $this;
    }

    /**
     * Bind args and parameters.
     *
     * @param array    $pram
     * @param array    $args
     *
     * @return array
     */
    protected function bindArgs(array $pram, array $args)
    {
        if (count($pram) == count($args)) {
            $newArgs = array_combine($pram, $args);
        } else {
            $newArgs = [];
            foreach ($pram as $p) {
                $newArgs[$p] = array_shift($args);
            }

            if (isset($args[0]) && count($args) == 1)
            {
                foreach (explode('/', '/'.$args[0]) as $arg) {
                    $newArgs[] = $arg;
                }
                $this->fullArg = $newArgs[0] = $args[0];
            }
            // pre($args);
            if(count($args)){
                $newArgs = array_merge($newArgs, $args);
            }
        }
        return $newArgs;
    }
    /**
     * Register a parameter name with validation from route uri.
     *
     * @param string      $uri
     * @param bool        $isGroup
     *
     * @return $patt
     */
    protected function namedParameters($uri, $isGroup = false)
    {
        // Reset pattern and parameters to empty array.
        $this->patt  = [];
        $this->prams = [];

        // Replace named parameters to regex pattern.
        return preg_replace_callback('/\/\{([a-z-0-9]+)\}\??(:\(?[^\/]+\)?)?/i', function($m) use ($isGroup)
        {
            // Check whether validation has been set and whether it exists.
            if (isset($m[2])) {
                $rep = substr($m[2], 1);
                $patt = isset($this->pattern[ $rep ]) ? $this->pattern[ $rep ] : '/'.$rep;
            } else {
                $patt = $this->pattern['/?'];
            }
            // Check whether parameter is optional.
            if (strpos($m[0], '?') !== false) {
                $patt = str_replace('/(', '(/', $patt).'?';
            }

            if ($isGroup) {
                $this->isGroup      = true;
                $this->pramsGroup[] = $m[1];
                $this->pattGroup[]  = $patt;
            } else {
                $this->prams[]      = $m[1];
                $this->patt[]       = $patt;
            }

            return $patt;
        }, trim($uri));
    }

    /**
     * Prepare a regex pattern.
     *
     * @param string      $patt
     * @param bool        $strict
     *
     * @return string
     */
    protected function prepare($patt, $strict = true)
    {
        // Fix group if it has an optional path on start
        if(substr($patt, 0, 3) == '/(/'){
            $patt = substr($patt, 1);
        }

        return  '~^' .$patt. ($strict ? '$' : '') .'~i';
    }

    /**
     * Checks whether the current route matches the specified pattern.
     *
     * @param string  $patt
     * @param bool    $call
     *
     * @return bool
     */
    protected function matched($patt, $call = true)
    {
        if ( preg_match($patt, $this->req->path, $m) ) {
            if ($call) {
                $this->matched = true;
            }
            array_shift($m);
            $this->matchedArgs =  array_map([$this, 'trimSlash'], $m);
            return true;
        }
        return false;
    }

    /**
     * Remove duplicate slashes.
     *
     * @param string     $uri
     *
     * @return string
     */
    protected function removeDuplSlash($uri) {
        return preg_replace('/\/+/', '/', '/'.$uri);
    }

    /**
     * Trim slashes.
     *
     * @param string     $uri
     *
     * @return string
     */
    protected function trimSlash($uri) {
        return trim($uri, '/');
    }

    /**
     * Add pattern to the named parameters list.
     *
     * @param array    $patt key value  i.e ['key' => '/([a-z0-9_]+)']
     */
    public function addPattern(array $patt) {
        $this->pattern = array_merge($this->pattern, $patt);
    }

    /**
     * Set a route name.
     *
     * @param string  $name
     *
     * @return $this
     */
     public function _as($name)
     {
        $name = strtolower($name);
        if (array_key_exists($name, $this->routes))
        {
            throw new \Exception ("Route name ($name) already registered.");
        }

        $patt = $this->patt;
        $pram = $this->prams;
        // Merge group parameters with route parameters.
        if($this->isGroup){
            $patt = array_merge($this->pattGroup, $patt);
            if(count($patt) > count($pram)){
                $pram = array_merge($this->pramsGroup, $pram);
            }
        }

        // :param
        if ($cprams = count($pram)) {
            foreach ($pram as $k => $v) {
                $pram[$k] = '/:'.$v;
            }
        }

        $name = str_replace('/', '.', $name);

        // Replace pattern to named parameters.
        $replaced = $this->group.$this->currentUri;
        foreach ($patt as $k => $v) {
            $pos = strpos($replaced, $v);
            if ($pos !== false) {
                $replaced = substr_replace($replaced, $pram[$k], $pos, strlen($v));
            }
        }

        $this->routes[$name] = ltrim( $this->removeDuplSlash(strtolower($replaced)), '/');

        return $this;
    }

    /**
     * Set a group name.
     *
     * @param string      $as
     */
    public function setGroupAs($as)
    {
        $this->groupAs = $as;
    }

    /**
     * Get a group name.
     *
     * @return string
     */
    public function getGroupAs()
    {
        return $this->groupAs;
    }

    /**
     * Register a new listener into the specified event.
     *
     * @param string      $name
     * @param array       $args
     *
     * @return string|null
     */
    public function getRoute($name, array $args = [])
    {
        $name = strtolower($name);

        if (isset($this->routes[$name])) {
            $route = $this->routes[$name];

            foreach ($args as $k => $v) {
                $route = str_replace(':'.$k, $v, $route);
            }
            return $route;
        }
        return null;
    }

    /**
     * Run and get a response.
     */
    public function end()
    {
        ob_start();
        if ($this->matched) {
            isset($this->routeCallback) && $this->callback($this->routeCallback, $this->req->args);
        } else {
            http_response_code(404);
            print('<h1>404 Not Found</h1>');
        }

        ob_end_flush(); exit;
    }

    /**
     * Call a route that has been matched.
     *
     * @param string\array    $callback
     * @param array           $args
     *
     * @return string
     */
    protected function callback($callback, array $args = [])
    {
        if (isset($callback))
        {
            if ( is_callable($callback) && $callback instanceof \Closure)
            {
                // Set new object and append the callback with some data.
                $o = new \ArrayObject($args);
                $o->app = App::instance();
                $callback = $callback->bindTo($o);
            }
            elseif (is_string($callback) && strpos($callback, '@') !== false)
            {
                $fixcallback       = explode('@', $callback, 2);
                $this->Controller  = $fixcallback[0];

                if ( is_callable(
                    $callback     = [ $fixcallback[0], (isset($fixcallback[1]) ? $fixcallback[1] : 'index') ]
                ) ) {
                    $this->Method = $callback[1];
                } else {
                    throw new \Exception("Callable error on {$callback[0]} -> {$callback[1]} !");
                }
            } else {
                throw new \Exception("Callable error on {$callback[0]} -> {$callback[1]} try with namespace");
            }

            if(is_array($callback) && !is_object($callback[0])){
                $callback[0] = new $callback[0];
            }

            if(isset($args[0]) && $args[0] == $this->fullArg){
                array_shift($args);
            }

            // Finally, call the method.
            return call_user_func_array($callback, $args);
        }
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
        switch (strtoupper($method)) {
            case 'AS':
                return call_user_func_array([$this, '_as'], $args);
            case 'ANY':
                array_unshift($args, []);
                return call_user_func_array([$this, 'route'], $args);
        }
        // Check whether the method is dynamic (i.e.: get, post, get_post).
        $method = explode('_', $method);
        $exists = [];
        foreach ($method as $v) {
            if (in_array($v = strtoupper($v), ['POST', 'GET', 'PUT','PATCH','DELETE'])) {
                $exists[] = $v;
            }
        }

        if (count($exists)) {
            array_unshift($args, $exists);
            return call_user_func_array([$this, 'route'], $args);
        }

        return  is_string($method) && isset($this->{$method}) && is_callable($this->{$method})
                ? call_user_func_array($this->{$method}, $args) : null;
    }

    /**
     * Set new variables and functions to this class.
     *
     * @param string      $k
     * @param mixed       $v
     */
    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
    }

}
