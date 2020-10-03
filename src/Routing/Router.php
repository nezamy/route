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
namespace Just\Routing;

use Just\DataType\Uri;
use Just\Http\Auth;
use Just\Http\Auth\AuthInterface;
use Just\Http\Middleware;
use Just\Http\Request;
use Just\Http\Response;
use Just\Prototype\ArrayPrototype;
use Just\Support\Regex;

/**
 * Class Route.
 * @method Route any(string $uri, mixed $handler, array $options = [])
 * @method Route get(string $uri, mixed $handler, array $options = [])
 * @method Route head(string $uri, mixed $handler, array $options = [])
 * @method Route post(string $uri, mixed $handler, array $options = [])
 * @method Route put(string $uri, mixed $handler, array $options = [])
 * @method Route patch(string $uri, mixed $handler, array $options = [])
 * @method Route options(string $uri, mixed $handler, array $options = [])
 * @method Route delete(string $uri, mixed $handler, array $options = [])
 */
class Router
{
    protected array $allowedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'DELETE', 'ANY'];

    protected array $allowedContentType = ['html' => 'text/html', 'json' => 'application/json', 'jsonp' => 'application/javascript'];

    protected Route $matched;

    private Request $request;

    private Response $response;

    private RouteHandlerInterface $handler;

    private RouteParserInterface $parser;

    private array $middleware_list = [];

    private array $globalMiddleware = [];

    private array $routes = [];

    private string $currentGroupPrefix = '';

    private array $currentGroupOptions = [];

    private array $nextGroupOptions = [];

    private array $groupOptions = [];

    private ?string $localeRedirectHandle = null;

    private array $auth_list = [];

    private ?string $currentAuthId = null;

    private \Closure $notfound;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->parser = new RouteParser();
        $this->handler = new RouteHandler();

        $this->notfound = function () {
            pre('Page is not found', '404 Not Found', 6);
        };
        container()->set(Request::class, $this->request);
        container()->set(Response::class, $this->response);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this, 'add'], array_merge([$method], $args));
    }

    public function setHandler(RouteHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function setParser(RouteParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function setNotfound($handler)
    {
        $this->notfound = $handler;
    }

    public function run(): Response
    {
        $this->match($this->request->method(), $this->request->uri());
        return $this->response;
    }

    public function add($method, $uri, $handler, array $options = []): Route
    {
        $uri = new Uri($this->currentGroupPrefix . $uri);
        $chunk = $uri->getChunk();

        if (! isset($this->routes[$chunk])) {
            $this->routes[$chunk] = [];
        }

        if (! in_array($method = strtoupper($method), $this->allowedMethods)) {
            throw new \LogicException("[{$method}] Method is not allowed");
        }
        if($method == 'ANY'){
            $method = '';
        }
        $opt = [];
        if ($this->currentAuthId) {
            $opt['auth'] = $this->currentAuthId;
        }
        if ($options) {
            $opt = array_merge($opt, $options);
        }
        if ($this->currentGroupOptions) {
            $opt['group'] = $this->currentGroupOptions;
        }

        $this->routes[$chunk][] = $route = new Route($method, $uri, $handler, $opt);

        return $route;
    }

    public function locale(array $locales, callable $callback): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }
        if (! isset($_SESSION['locale'])) {
            $_SESSION['locale'] = $locales[0];
        }
        $this->group('/{@locale}?:(' . implode('|', $locales) . ')', $callback);
    }

    public function auth(AuthInterface $auth, callable $callback)
    {
        $id = uniqid((string) rand());
        $this->auth_list[$id] = $auth;
        $this->currentAuthId = $id;
        $callback($this);
        $this->currentAuthId = '';
    }

    public function group($prefix, callable $callback): void
    {
        $previousGroupOptions = $this->currentGroupOptions;
        if ($this->nextGroupOptions) {
            $id = uniqid((string) rand());
            $this->groupOptions[$id] = $this->nextGroupOptions;
            $this->currentGroupOptions[] = $id;
            $this->nextGroupOptions = [];
        }

        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupOptions = $previousGroupOptions;
    }

    public function middleware(...$middleware): Router
    {
        $id = uniqid((string) rand());
        $this->middleware_list[$id] = $middleware;
        if (! isset($this->nextGroupOptions['middleware'])) {
            $this->nextGroupOptions['middleware'] = [];
        }
        $this->nextGroupOptions['middleware'][] = $id;
        return $this;
    }

    public function use(...$middleware): Router
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        return $this;
    }

    public function addPlaceholders(array $patterns): void
    {
        foreach ($patterns as $name => $pattern) {
            Regex::set($name, $pattern);
        }
    }

    public function match(string $method, string $uri): bool
    {
        $uri = new Uri($uri);
        $uri->rtrim('/');
        $chunk = $uri->getChunk();
        $matched = null;

        if (isset($this->routes[$chunk])) {
            $matched = $this->find($method, $uri, $this->routes[$chunk]);
        }
        if (! $matched && isset($this->routes['/*'])) {
            $matched = $this->find($method, $uri, $this->routes['/*']);
        }

        if ($matched) {
            $this->handleOptions($matched);

            if ($this->response->isEnded()) {
                return false;
            }

            $args = $matched->getArgs();
            if (count($args)) {
                container()->importVars($args);
                $this->request->setArguments($args);
            }

            $middleware = new Middleware($this->handler);
            $middleware->add($matched->getMiddleware());

            $output = $middleware->handle(
                $matched->getHandler($this->handler)
            );

            $this->response->write($output ?? '');
            return true;
        }

        $this->handler->call($this->notfound);
        return false;
    }

    public function handleMiddleware(Route $matched)
    {
        if ($this->globalMiddleware) {
            foreach ($this->globalMiddleware as $item) {
                $matched->middleware($item);
            }
        }

        if ($matched->options->has('middleware')) {
            $middleware = (array) $matched->options->get('middleware');
            foreach (array_reverse($middleware) as $item) {
                call_user_func_array([$matched, 'middleware'], $this->middleware_list[$item]);
            }
        }
    }

    public function getMatched()
    {
        return $this->matched;
    }

    public function export(): array
    {
        return $this->routes;
    }

    private function handleOptions(Route $matched)
    {
        if ($matched->options->has('auth')) {
            $this->handleAuth($matched->options->get('auth'));
        }

        if ($matched->options->has('group')) {
            $this->handleGroupOptions($matched->options);
        }

        $this->handleMiddleware($matched);

        if ($matched->options->has('namespace')) {
            $matched->addNamespaceToHandler();
        }
        //Handle Json
        if ($matched->options->has('content_type') && isset($this->allowedContentType[$matched->options->get('content_type')])) {
            $this->response->headers->set('Content-Type', $this->allowedContentType[$matched->options->get('content_type')]);
        }
    }

    private function handleAuth(string $id)
    {
        if (! isset($this->auth_list[$id])) {
            throw new \LogicException('Auth is not Registered');
        }
        $auth = new Auth($this->auth_list[$id]);
        $this->request->setUser($auth->user());
    }

    private function handleGroupOptions(ArrayPrototype $options)
    {
        $final = [];
        foreach ($options->get('group') as $id) {
            foreach ($this->groupOptions[$id] as $key => $value) {
                if ($key == 'namespace') {
                    $final['namespace'] = (isset($final['namespace']) ? $final['namespace'] . '\\' : '') . $value;
                    continue;
                }
                if ($key == 'middleware') {
                    $final['middleware'] = array_merge($final['middleware'] ?? [], $value);
                    continue;
                }
                $final[$key] = $value;
            }
        }
        $options->remove('group');
        $options->add($final);
    }

    private function find(string $requestMethod, Uri $requestUri, $routes): Route
    {
        $result_args = [];
        $matched = false;
        foreach ($routes as $route) {
            $uri = $route->getUri();
            $method = $route->getMethod();
            $uri->rtrim('/');
            if ($method && $method !== $requestMethod) {
                continue;
            }

            if ($uri->isStatic() && $uri->eq((string) $requestUri)) {
                $matched = true;
            } else {
                $pattern = $this->parser->parse((string) $uri);
                if (preg_match('~^' . $pattern['result'] . '$~i', (string) $requestUri, $args)) {
                    array_shift($args);
                    if ($args) {
                        $args = array_map(function ($s) {
                            return ltrim($s, '/');
                        }, $args);
                    }
                    $result_args = $this->bindArgs($pattern['parameters'], $args);
                    $matched = true;
                }
            }

            if ($matched) {
                $route->setArgs($result_args);
                $this->matched = $route;
                return $route;
            }
        }
        return new Route('', new Uri('/404'), $this->notfound);
    }

    private function bindArgs(array $pram, array $args): array
    {
        $newArgs = [];
        if (count($pram) == count($args)) {
            $pram = array_map(function ($s) {
                return ltrim($s, '@');
            }, $pram);
            $newArgs = array_combine($pram, $args);
        } else {
            foreach ($pram as $p) {
                $value = array_shift($args);
                if ($p == '@locale') {
                    $p = 'locale';
                    if (! $value) {
                        $this->localeRedirect();
                        break;
                    }
                }
                $newArgs[$p] = $value;
            }
        }
        if (isset($newArgs['locale'])) {
            $_SESSION['locale'] = $newArgs['locale'];
        }
        return $newArgs;
    }

    private function localeRedirect()
    {
        $value = $_SESSION['locale'];
        $this->response->redirectTo('/' . $value . $this->request->uri());
        if ($this->localeRedirectHandle && is_callable($this->localeRedirectHandle)) {
            $redirect = call_user_func_array($this->localeRedirectHandle, [$this->response->getRedirect(), $this->request->uri()]);
            if ($redirect) {
                $this->response->redirectTo('/' . $value . $this->request->uri());
            }
        }
        $this->response->end();
    }
}
