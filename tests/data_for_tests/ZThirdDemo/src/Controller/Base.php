<?php declare(strict_types=1);
/**
 * ZThirdDemo - controller base of the main app.
 */
namespace ZThirdDemo\Controller;

use DuckPhp\Foundation\SingletonTrait;

abstract class Base
{
    use SingletonTrait;
    public function __construct()
    {
    }
}
