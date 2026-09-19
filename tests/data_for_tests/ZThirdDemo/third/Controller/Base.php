<?php declare(strict_types=1);
/**
 * ZThirdDemo - controller base of the third party app.
 */
namespace ZThirdDemo\Third\Controller;

use DuckPhp\Foundation\SingletonTrait;

abstract class Base
{
    use SingletonTrait;
    public function __construct()
    {
    }
}
