<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\SingletonTrait;

class SingletonTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SingletonTrait::class);
        
        SingletonObject::_();
        
        \LibCoverage\LibCoverage::End();

    }
}
class SingletonObject
{
    use SingletonTrait;
}