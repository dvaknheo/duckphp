<?php declare(strict_types=1);
/**
 * DuckPhp
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\SingletonTrait;

abstract class Base
{
    use SingletonTrait;
    public function __construct()
    {
    }
}
