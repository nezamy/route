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

class ArrayPrototype
{
    use Getter;
    use ConvertObject;
    use Setter;

    public function __construct(array $data = [])
    {
        $this->replace($data);
        return $this;
    }

//    public function set(string $key, $value): void {
//        if(!$value instanceof StringPrototype){
//            $type = gettype($value);
//            switch ($type) {
//                case 'string':
//                    $value = new StringPrototype($value);
//                    break;
//                case 'array':
//                case 'object':
//                    $value = new ArrayPrototype((array)$value);
//            }
//        }
//        $this->_set($key, $value);
//    }
}
