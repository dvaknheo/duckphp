<?php 
namespace tests\DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\JsonRpcClientBase;
use DuckPhp\Ext\JsonRpcExt;

class JsonRpcClientBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(JsonRpcClientBase::class);
        
        JsonRpcExt::G(JsonRpcClientBaseJsonRpcExt::G());
        
        JsonRpcExt::Wrap(JsonRpcClientBaseObject::class);
        
        JsonRpcClientBaseObject::G()->init([])->isInited();
        JsonRpcClientBaseObject::G()->foo();
        
        \LibCoverage\LibCoverage::End();
        /*
        JsonRpcClientBase::G()->__call($method, $arguments);
        //*/
    }
}
class JsonRpcClientBaseJsonRpcExt extends JsonRpcExt
{
    public function getRealClass($object)
    {
        return "Mocked";
    }
    public function callRpc($classname, $method, $arguments)
    {
        var_dump(func_get_args());
        return;
    }
}
class JsonRpcClientBaseObject extends ComponentBase
{
    
}
