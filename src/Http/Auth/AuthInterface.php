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
namespace Just\Http\Auth;

interface AuthInterface
{
    public function check(): bool;

    public function validate(array $credentials): bool;

    public function redirectToLogin();

    public function login(): bool;

    public function logout(): void;

    public function user();
}
