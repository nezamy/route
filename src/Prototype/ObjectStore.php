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
namespace Just\Prototype;

class ObjectStore
{
    use Setter;
    use Getter;
    use ConvertObject;

    public function __construct(array $data = [])
    {
        $this->replace($data);
    }
}
