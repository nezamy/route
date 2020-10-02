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
namespace Just\Http;

use Just\Prototype\GetterObject;

/**
 * Class Request.
 * @method array arguments()
 * @method string uri()
 * @method string method()
 * @method string currentUrl()
 * @method string protocol()
 * @method string scheme()
 * @method array user()
 */
class Request
{
    public GetterObject $headers;

    public $server = [];

    public $cookies = [];

    public $query = [];

    public $body = [];

    public $files = [];

    public $tmp_files = [];

    protected $_arguments = [];

    protected $_uri;

    protected $_method;

    protected $_url;

    protected $_currentUrl;

    protected $_protocol;

    protected $_scheme;

    protected $_user = [];

    public function __construct(array $headers, array $server, array $cookies, array $query, array $body, array $files, $tmp_files = [])
    {
        $this->headers = new class($headers) extends GetterObject {
            protected function getterTransformKey($key): string
            {
                return  str_replace([' ', '_'], '-', strtolower($key));
            }
        };
        $this->server = new class($server) extends GetterObject {
            protected function getterTransformKey($key): string
            {
                return strtolower($key);
            }
        };

        $this->cookies = new GetterObject($cookies);
        $this->query = new GetterObject($query);
        $this->body = new GetterObject($body);
        $this->files = new GetterObject($files);
        $this->tmp_files = new GetterObject($tmp_files);
        $this->_protocol = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';
        $this->_scheme = isset($server['https']) && $server['https'] === 'on' || isset($headers['x-forwarded-proto']) && $headers['x-forwarded-proto'] == 'https' ? 'https' : 'http';
        $this->_uri = urldecode(parse_url($server['request_uri'] ?? '', PHP_URL_PATH) ?? '') ?? '/';
        $this->_url = $this->_scheme . '://' . $this->serverName();
        $this->_currentUrl = $this->url(rtrim($this->_uri, '/') ?? '');
        $this->_method = $server['request_method'] ?? 'GET';
    }

    public function __call($method, $args)
    {
        if (isset($this->{'_' . $method})) {
            return $this->{'_' . $method};
        }
        throw new \LogicException("{$method} Not Exists on Just\\Http\\Request");
    }

    public function url($uri = ''): string
    {
        return $this->_url . $uri;
    }

    public function serverName(): string
    {
        return $this->server->get('server_name', $this->headers->get('host', 'localhost'));
    }

    public function setArguments(array $args): void
    {
        $this->_arguments = $args;
    }

    public function setUser(array $user): void
    {
        $this->_user = $user;
    }

    public function isJson(): bool
    {
        if ($this->headers->get('X_REQUESTED_WITH') == 'XMLHttpRequest' || strpos($this->headers->get('ACCEPT'), '/json') !== false) {
            return true;
        }
        return false;
    }

    public function ip(): string
    {
        if ($this->headers->has('client_ip')) {
            $ip = $this->headers->get('client_ip');
        } elseif ($this->headers->has('x_forwarded_for')) {
            $ip = $this->headers->get('x_forwarded_for');
        } elseif ($this->headers->has('x_forwarded')) {
            $ip = $this->headers->get('x_forwarded');
        } elseif ($this->headers->has('forwarded_for')) {
            $ip = $this->headers->get('forwarded_for');
        } elseif ($this->headers->has('forwarded')) {
            $ip = $this->headers->get('forwarded');
        } elseif ($this->server->has('remote_addr')) {
            $ip = $this->server->get('remote_addr');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'unknown';
        }

        return $ip;
    }

    public function browser(): string
    {
        $user_agent = $this->headers->get('user-agent');
        if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
            return 'Opera';
        }
        if (strpos($user_agent, 'Edge')) {
            return 'Edge';
        }
        if (strpos($user_agent, 'Chrome')) {
            return 'Chrome';
        }
        if (strpos($user_agent, 'Safari')) {
            return 'Safari';
        }
        if (strpos($user_agent, 'Firefox')) {
            return 'Firefox';
        }
        if (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
            return 'Internet Explorer';
        }
        return 'unknown';
    }

    public function platform(): string
    {
        $user_agent = $this->headers->get('user-agent');
        if (preg_match('/linux/i', $user_agent)) {
            return 'linux';
        }
        if (preg_match('/macintosh|mac os x/i', $user_agent)) {
            return 'mac';
        }
        if (preg_match('/windows|win32/i', $user_agent)) {
            return 'windows';
        }
        return 'unknown';
    }

    public function isMobile(): bool
    {
        $aMobileUA = [
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
        ];

        // Return true if mobile User Agent is detected.
        foreach ($aMobileUA as $sMobileKey => $sMobileOS) {
            if (preg_match($sMobileKey, $this->headers->get('user-agent'))) {
                return true;
            }
        }
        // Otherwise, return false.
        return false;
    }
}
