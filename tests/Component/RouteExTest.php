<?php
namespace tests\DuckPhp\Component
{

use DuckPhp\Component\RouteEx;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\SuperGlobalContext;

class RouteExTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteEx::class);
        
        RouteEx::G()->init([]);
        \LibCoverage\LibCoverage::End();
    }
}

}
namespace tests_Core_RouteEx
{

}