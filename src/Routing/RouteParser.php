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

use Just\Support\Regex;

class RouteParser implements RouteParserInterface
{
    public function parse(string $uri): array
    {
        $matchedParameter = [];
        $matchedPattern = [];
        $result = preg_replace_callback('/\/\{([a-z-0-9@]+)\}\??((:\(?[^\/]+\)?)?)/i', function ($match) use (&$matchedParameter, &$matchedPattern) {
            [$full, $parameter, $namedPattern] = $match;
            $pattern = '/' . Regex::get('?');
            if (! empty($namedPattern)) {
                $replace = substr($namedPattern, 1);

                if (Regex::has($replace)) {
                    $pattern = '/' . Regex::get($replace);
                } elseif (substr($replace, 0, 1) == '(' && substr($replace, -1, 1) == ')') {
                    $pattern = '/' . $replace;
                }
            } elseif (Regex::has($parameter)) {
                $pattern = '/' . Regex::get($parameter);
            }
            // Check whether parameter is optional.
            if (strpos($full, '?') !== false) {
                $pattern = str_replace(['/(', '|'], ['(/', '|/'], $pattern) . '?';
            }
            $matchedParameter[] = $parameter;
            $matchedPattern[] = $pattern;

            return $pattern;
        }, trim($uri));

        return ['parameters' => $matchedParameter, 'patterns' => $matchedPattern, 'result' => $result];
    }
}
