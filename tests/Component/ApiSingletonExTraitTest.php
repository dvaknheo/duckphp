<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\ApiSingletonExTrait;
use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;

class ApiSingletonExTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(ApiSingletonExTrait::class);
        ApiSingletonExTraitObject::G();
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class ApiSingletonExTraitObject
{
    use ApiSingletonExTrait;
}
class MainApp2 extends DuckPhp
{
}
class SubApp2 extends DuckPhp
{
}