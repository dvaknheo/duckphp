<?php declare(strict_types=1);
/**
 * DuckPhp
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\SimpleControllerTrait;

abstract class Base
{
    use SimpleControllerTrait;
    public function __construct()
    {
    }
}
