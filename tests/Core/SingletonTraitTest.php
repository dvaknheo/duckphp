<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SingletonExTrait;

class SingletonTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SingletonExTrait::class);
        
        SingletonObject::_();
        
        \LibCoverage\LibCoverage::End();

    }
}
class SingletonObject
{
    use SingletonExTrait;
}