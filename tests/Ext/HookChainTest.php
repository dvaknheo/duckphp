<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\HookChain;

class HookChainTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(HookChain::class);
        
        $hooks=null;
        $callable1=function(){ var_dump(DATE(DATE_ATOM));};
        $callable2=function(){ var_dump(DATE(DATE_ATOM));return true;};
        $callable3=function(){ var_dump(DATE(DATE_ATOM));return true;};
        HookChain::Hook($hooks, $callable1, $append = true, $once = true);
        HookChain::Hook($hooks, $callable2, $append = true, $once = true);
        
        ($hooks)();
        $hooks->all();
        $hooks->has($callable2);
        $hooks->remove($callable2);
        $hooks->add($callable1,false,true);
        $hooks->add($callable3,false,false);
        
        ////////////////
        $t=($hooks[0]);
        $hooks[]=$callable2;
        $hooks[1]=$callable2;
        if(isset($hooks[1])){unset($hooks[1]);}
        
        $hooks2=function(){var_dump("!!!!");};
        HookChain::Hook($hooks2, $callable1);
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzz";
        ($hooks2)();
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzz";

        \LibCoverage\LibCoverage::End(HookChain::class);
        $this->assertTrue(true);
        /*
        HookChain::_()->__invoke();
        HookChain::_()->Hook($var, $callable, $append = true, $once = true);
        HookChain::_()->add($callable, $append, $once);
        HookChain::_()->remove($callable);
        HookChain::_()->has($callable);
        HookChain::_()->all();
        HookChain::_()->offsetSet($offset, $value);
        HookChain::_()->offsetExists($offset);
        HookChain::_()->offsetUnset($offset);
        HookChain::_()->offsetGet($offset);
        //*/
    }
}
