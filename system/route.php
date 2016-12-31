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
     * Named parameters list
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
     * Constractor - define some variables
     */
    public function __construct(Request $req)
    {
        $this->routes         = $this->urls = [];
        $this->group          = $this->matchedPath = '';
        $this->matched        = false; //if uri exists
        $this->pramsGroup     = [];
        $this->groupAs        = '';
        $this->isGroup        = false;
        $this->req            = $req;
        $this->roles          = [];
    }

    /**
     * Singleton instance
     *
     * @return  $this
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Regsiter a route with callback
     *
     * @param   array           $method
     * @param   string|array    $uri
     * @param   callable        $callback
     * @param   array           $options
     *
     * @return  $this
     */
    public function route (array $method, $uri, $callback, $options = [])
    {
        if ( is_array($uri) ) {
            foreach ( $uri as $u ) {
                $this->route($method, $u, $callback, $options);
            }
            return $this;
        }

        $options = array_merge(['ajaxOnly' => false], $options);

        if($uri != '/'){
            $uri = $this->trimc($uri).'/';
        }

        // replace named uri pram to regx pattern
        $pattern  = $this->namedParameters($uri);

        //clean uri for route name
        // $this->currentUri = trim( str_replace($this->patt, '', $pattern) ,'/').'/';
        $this->currentUri = $pattern;

        if ($options['ajaxOnly'] == false || $options['ajaxOnly'] && $this->req->ajax)
        {
            //if matched before skip this
            if ($this->matched === false)
            {
                //prepare
                $pattern = $this->prepare(
                    str_replace(['/?', '/*'], [ $this->pattern['/?'], $this->pattern['/*'] ], $this->group.$pattern)
                );

                //if matched
                $method = count($method) > 0 ? in_array($this->req->method, $method) : true;
                if ( $method && ( $args = $this->matched($pattern) ) !== false )
                {
                    if($this->isGroup){
                        $this->prams = array_merge($this->pramsGroup, $this->prams);
                    }

                    if (count($this->prams) == count($args)) {
                        $this->req->args = array_combine($this->prams, $args);
                    } else {
                        $this->req->args = [];
                        foreach ($this->prams as $key => $value) {
                            $this->req->args[$value] = array_shift($args);
                        }

                        if (isset($this->req->args[0]) && count($this->req->args) == 1)
                        {
                            foreach (explode('/', $this->req->args[0]) as $arg) {
                                $this->req->args[] = $arg;
                            }
                        }
                    }

                    $this->matchedPath   = $this->currentUri;
                    $this->routeCallback = $callback;
                    $this->saveit[]      = $this->getGroupAs().$this->matchedPath;
                }
            }
        }

        return $this;
    }

    /**
     * Group of routes
     *
     * @param   string|array    $group
     * @param   callable        $callback
     *
     * @return  $this
     */
    public function group($group, callable $callback) {
        if ( is_array($group) ) {
            foreach ( $group as $p ) {
                $this->group($p, $callback);
            }
            return $this;
        }

        $group = $this->trimc($group);
        $group = $this->namedParameters($group, true);

        // if ( $this->matched( $this->prepare($group, false), false ) !== false ) {
            $currentGroup     = $group;
            $this->group     .= $currentGroup;
            $callback         = $callback->bindTo($this);

            call_user_func_array($callback, $this->req->args);
            $this->isGroup     = false;
            $this->pramsGroup  = [];
            $this->group = substr($this->group, 0, -strlen($currentGroup));
        // }
        return $this;
    }

    /**
     * register a parameter name with validation from route uri
     *
     * @param   string      $uri
     * @param   bool        $isGroup
     *
     * @return  $patt
     */
    protected function namedParameters($uri, $isGroup = false)
    {
        //reset pattern and parameters to empty array
        $this->patt  = [];
        $this->prams = [];

        // replace named parameters to regx pattern
        return preg_replace_callback('/\/\{([a-z-0-9]+)\}\??(:\(?[^\/]+\)?)?/i', function($m) use ($isGroup)
        {
            if ($isGroup) {
                $this->isGroup      = true;
                $this->pramsGroup[] = $m[1];
            } else {
                $this->prams[] = $m[1];
            }
            //check if validation is seted and exists or not
            if (isset($m[2])) {
                $rep = substr($m[2], 1);
                $patt = isset($this->pattern[ $rep ]) ? $this->pattern[ $rep ] : '/'.$rep;
            } else {
                $patt = $this->pattern['/?'];
            }
            //check if parameter is optional
            if (strpos($m[0], '?') !== false) {
                $patt = str_replace('/(', '(/', $patt).'?';
            }

            return $this->patt[] = $patt;
        }, trim($uri));
    }

    /**
     * prepare a regx pattern
     *
     * @param   string      $patt
     * @param   bool        $strict
     *
     * @return  string
     */
    protected function prepare($patt, $strict = true)
    {
        //fix group if has optional path on start
        if(substr($patt, 0, 3) == '/(/'){
            $patt = substr($patt, 1);
        }

        return  '~^' .$patt. ($strict ? '$' : '') .'~i';
    }

    /**
     * whether the current route matches the specified pattern or not
     *
     * @param   string  $patt
     * @param   bool    $call
     *
     * @return  bool
     */
    protected function matched($patt, $call = true)
    {
        if ( preg_match($patt, $this->req->path, $m) ) {
            if ($call) {
                $this->matched = true;
                array_shift($m);
                return array_map([$this, 'trimc'], $m);
            }
            return true;
        }
        return false;
    }

    /**
     * Remove the duplicated slashes
     *
     * @param   string     $uri
     *
     * @return  string
     */
    protected function trimc($uri) {
        return preg_replace('/\/+/', '/', '/'.trim($uri,'/'));
    }

    /**
     * Add pattern to named parameters list
     *
     * @param   array    $patt key value  i.e ['key' => '/([a-z0-9_]+)']
     */
    public function addPattern(array $patt) {
        $this->pattern = array_merge($this->pattern, $patt);
    }

    /**
     * Set a route name
     *
     * @param   string  $name
     *
     * @return  $this
     */
     public function _as($name)
     {
        $name = strtolower($name);

        if (array_key_exists($name, $this->routes))
        {
            throw new \Exception ("Route name ($name) already registered.");
        }

        if (count($this->prams)) {
            foreach ($this->prams as $k => $v) {
                $this->prams[$k] = '/:'.$v;
            }
        }

        $name = str_replace('/', '.', $name);
        $this->routes[$name] = strtolower(str_replace($this->patt, $this->prams, $this->group.$this->currentUri));

        return $this;
    }

    /**
     * Set a group name
     *
     * @param   string      $as
     */
    public function setGroupAs($as)
    {
        $this->groupAs = $as;
    }

    /**
     * Get a group name
     *
     * @return  string
     */
    public function getGroupAs()
    {
        return $this->groupAs;
    }

    /**
     * register a new listener into the specified event
     *
     * @param   string      $name
     * @param   array       $args
     *
     * @return  string|null
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
            // return rtrim($route,'/').'/';
        }
        return null;
    }

    /**
     * Run and get a response
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
     * Call a route has been matched
     *
     * @param   string\array    $callback
     * @param   array           $args
     *
     * @return  string
     */
    protected function callback($callback, array $args = [])
    {
        if (isset($callback))
        {
            if ( is_callable($callback) && !is_array($callback) )
            {
                //Set new object and append the callback with some data
                $o = new \ArrayObject($this->req->args);
                $callback = $callback->bindTo($o);
            }
            elseif (!is_array($callback))
            {
                $fixcallback       = str_replace(['.','@','->'],'@', $callback);
                $fixcallback       = explode('@',$fixcallback,2);
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

            //Finaly call the method
            return call_user_func_array($callback, $args);
        }
    }

    /**
     * magic call
     *
     * @param   string   $method
     * @param   array    $args
     *
     * @return  mixed
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
        //check if dynamic method i.e (get - post - get_post)
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

        return  isset($this->{$method}) && is_callable($this->{$method})
                ? call_user_func_array($this->{$method}, $args) : null;
    }

    /**
     * Set new variables and functions to this class
     *
     * @param   string      $k
     * @param   mixed       $v
     */
    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof Closure ? $v->bindTo($this) : $v;
    }

}
