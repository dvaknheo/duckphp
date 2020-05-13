<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ComponentInterface;

class ComponentBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ComponentBase::class);

        ComponentBaseObject::G()->init(['a'=>'b'],new \stdClass());
        ComponentBaseObject::G()->isInited();

        \MyCodeCoverage::G()->end();
        $this->assertTrue(true);
    }
}

class ComponentBaseObject extends ComponentBase  implements ComponentInterface
{

}