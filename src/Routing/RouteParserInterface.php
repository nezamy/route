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

interface RouteParserInterface
{
    /**
     * @param string $uri i.e '/{username}:([0-9a-z_.-]+)/post/{id}:([0-9]+)'
     * @return array [
     *               'parameters' => ['username', 'id'],
     *               'patterns' => ['/([0-9a-z_.-]+)', '/([0-9]+)'],
     *               'result' => '/([0-9a-z_.-]+)/post/([0-9]+)'
     *               ]
     */
    public function parse(string $uri): array;
}
