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
 * Request
 *
 * @package     Just Framework
 * @author      Mahmoud Elnezamy <http://nezamy.com>
 * @since       1.0.0
 */

class Request
{
    private static $instance;

    /**
     * Constructor - Define some variables.
     */
    public function __construct()
    {
        $this->server = $_SERVER;

        $uri = parse_url($this->server["REQUEST_URI"], PHP_URL_PATH);
        $script = $_SERVER['SCRIPT_NAME'];
        $parent = dirname($script);

        // Fix path if not running on domain or local domain.
        if (stripos($uri, $script) !== false) {
            $this->path = substr($uri, strlen($script));
        } elseif (stripos($uri, $parent) !== false) {
            $this->path = substr($uri, strlen($parent));
        } else {
            $this->path = $uri;
        }

        $this->path = preg_replace('/\/+/', '/', '/' . trim(urldecode($this->path), '/') . '/');
        $this->hostname = str_replace('/:(.*)$/', "", $_SERVER['HTTP_HOST']);
        $this->servername = empty($_SERVER['SERVER_NAME']) ? $this->hostname : $_SERVER['SERVER_NAME'];
        $this->secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
        $this->port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
        $this->protocol = $this->secure ? 'https' : 'http';
        $this->url = strtolower($this->protocol . '://' . $this->servername);
        if($this->servername == 'localhost'){
            $this->url = strtolower(
                $this->protocol . '://' . $this->servername . 
                str_replace($this->path, '', $this->server['REQUEST_URI'])
            );
        }
        $this->curl = rtrim($this->url, '/') . $this->path;
        $this->extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $this->headers = call_user_func(function () {
            $r = [];
            foreach ($_SERVER as $k => $v) {
                if (stripos($k, 'http_') !== false) {
                    $r[strtolower(substr($k, 5))] = $v;
                }
            }
            return $r;
        });
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->query = $_GET;
        $this->args = [];
        foreach ($this->query as $k => $v) {
            $this->query[$k] = preg_replace('/\/+/', '/', str_replace(['..', './'], ['', '/'], $v));
        }

        if (isset($this->headers['content_type']) && $this->headers['content_type'] == 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents("php://input"), $input);
        } else {
            $input = json_decode(file_get_contents("php://input"), true);
        }

        $this->body = is_array($input) ? $input : [];
        $this->body = array_merge($this->body, $_POST);
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->cookies = $_COOKIE;
        $x_requested_with = isset($this->headers['x_requested_with']) ? $this->headers['x_requested_with'] : false;
        $this->ajax = $x_requested_with === 'XMLHttpRequest';
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
     * Get user IP.
     *
     * @return string
     */
    public function ip()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            $ip = $_SERVER["HTTP_FORWARDED"];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = getenv("REMOTE_ADDR");
        }
        
        if(strpos($ip, ',') !== false){
            $ip = explode(',', $ip)[0];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'unknown';
        }

        return $ip;
    }

    /**
     * Get user browser.
     *
     * @return string
     */
    public function browser()
    {
        if (strpos($this->server['HTTP_USER_AGENT'], 'Opera') || strpos($this->server['HTTP_USER_AGENT'], 'OPR/')) {
            return 'Opera';
        } elseif (strpos($this->server['HTTP_USER_AGENT'], 'Edge')) {
            return 'Edge';
        } elseif (strpos($this->server['HTTP_USER_AGENT'], 'Chrome')) {
            return 'Chrome';
        } elseif (strpos($this->server['HTTP_USER_AGENT'], 'Safari')) {
            return 'Safari';
        } elseif (strpos($this->server['HTTP_USER_AGENT'], 'Firefox')) {
            return 'Firefox';
        } elseif (strpos($this->server['HTTP_USER_AGENT'], 'MSIE') || strpos($this->server['HTTP_USER_AGENT'], 'Trident/7')) {
            return 'Internet Explorer';
        }
        return 'unknown';
    }

    /**
     * Get user platform.
     *
     * @return string
     */
    public function platform()
    {
        if (preg_match('/linux/i', $this->server['HTTP_USER_AGENT'])) {
            return 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $this->server['HTTP_USER_AGENT'])) {
            return 'mac';
        } elseif (preg_match('/windows|win32/i', $this->server['HTTP_USER_AGENT'])) {
            return 'windows';
        }
        return 'unknown';
    }

    /**
     * Check whether user has connected from a mobile device (tablet, etc).
     *
     * @return bool
     */
    public function isMobile()
    {
        $aMobileUA = array(
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        // Return true if mobile User Agent is detected.
        foreach ($aMobileUA as $sMobileKey => $sMobileOS) {
            if (preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])) {
                return true;
            }
        }
        // Otherwise, return false.
        return false;
    }

    /**
     * Magic call.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return isset($this->{$method}) && is_callable($this->{$method})
            ? call_user_func_array($this->{$method}, $args) : null;
    }

    /**
     * Set new variables and functions to this class.
     *
     * @param string $k
     * @param mixed $v
     */
    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
    }
}
