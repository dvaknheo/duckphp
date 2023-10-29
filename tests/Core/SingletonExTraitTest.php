<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SingletonExTrait;

class SingletonExTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SingletonExTrait::class);
        
        SingletonExObject::_();
        
        \LibCoverage\LibCoverage::End();

    }
}
class SingletonExObject
{
    use \DuckPhp\Core\SingletonExTrait;
}