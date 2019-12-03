<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\JsonRpcClientBase;
use DuckPhp\Ext\JsonRpcExt;

class JsonRpcClientBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(JsonRpcClientBase::class);
        
        //code here
        JsonRpcExt::G(JsonRpcClientBaseJsonRpcExt::G());
        $x=new JsonRpcClientBase();
        $x->foo();
        \MyCodeCoverage::G()->end(JsonRpcClientBase::class);
        $this->assertTrue(true);
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