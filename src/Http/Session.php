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

class Session
{
    private $options = [];

    public function __construct(array $options)
    {
        $this->options = array_merge([
            'storage',
        ], $options);
    }
}
