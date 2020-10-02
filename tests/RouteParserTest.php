<?php
declare(strict_types=1);

namespace Just\Test;

use Just\Routing\RouteParser;
use PHPUnit\Framework\TestCase;

class RouteParserTest extends TestCase
{
    public function testParameters() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{username}/post/{id}');
        $this->assertSame([
            'parameters' => [
                'username', 'id'
            ], 'patterns' => [
                '/([^\/]+)',
                '/([^\/]+)'
            ], 'result' => '/([^\/]+)/post/([^\/]+)'
        ], $results);
    }

    public function testParametersWithRegex() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{username}:([0-9a-z_.-]+)/post/{id}:([0-9]+)');
        $this->assertSame([
            'parameters' => [
                'username', 'id'
            ], 'patterns' => [
                '/([0-9a-z_.-]+)',
                '/([0-9]+)'
            ], 'result' => '/([0-9a-z_.-]+)/post/([0-9]+)'
        ], $results);
    }

    public function testParametersWithPlaceholder() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{username}:title/post/{id}:int');
        $this->assertSame([
            'parameters' => [
                'username', 'id'
            ], 'patterns' => [
                '/([a-z_-]+)',
                '/([0-9]+)'
            ], 'result' => '/([a-z_-]+)/post/([0-9]+)'
        ], $results);
    }

    public function testParametersWithPlaceholderAll() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{all}:*');
        $this->assertSame([
            'parameters' => [
                'all'
            ], 'patterns' => [
                '/(.*)',
            ], 'result' => '/(.*)'
        ], $results);
    }

    public function testParametersHasSamePlaceholderName() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/post/{title}');
        $this->assertSame([
            'parameters' => [
                'title'
            ], 'patterns' => [
                '/([a-z_-]+)',
            ], 'result' => '/post/([a-z_-]+)'
        ], $results);
    }

    public function testOptionalParametersWithPlaceholder() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{username}:title/posts/{id}?:int');
        $this->assertSame([
            'parameters' => [
                'username', 'id'
            ], 'patterns' => [
                '/([a-z_-]+)',
                '(/[0-9]+)?'
            ], 'result' => '/([a-z_-]+)/posts(/[0-9]+)?'
        ], $results);
    }

    public function testLocaleParameter() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{@locale}:(ar|en)/posts/{id}?:int');
        $this->assertSame([
            'parameters' => [
                '@locale', 'id'
            ], 'patterns' => [
                '/(ar|en)',
                '(/[0-9]+)?'
            ], 'result' => '/(ar|en)/posts(/[0-9]+)?'
        ], $results);
    }

    public function testLocaleOptionalParameter() : void
    {
        $parser = new RouteParser();
        $results = $parser->parse('/{@locale}?:(ar|en)/posts/{id}?:int');
        $this->assertSame([
            'parameters' => [
                '@locale', 'id'
            ], 'patterns' => [
                '(/ar|/en)?',
                '(/[0-9]+)?'
            ], 'result' => '(/ar|/en)?/posts(/[0-9]+)?'
        ], $results);
    }
}
