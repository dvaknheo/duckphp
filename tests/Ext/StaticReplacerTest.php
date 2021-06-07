<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\StaticReplacer;

class StaticReplacerTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(StaticReplacer::class);
        
        //code here        
        $k="k";$v="v";
        StaticReplacer::G()->_GLOBALS($k, $v=null);
        StaticReplacer::G()->_STATICS($k, $v=null);
        StaticReplacer::G()->_CLASS_STATICS(StaticReplacer_SimpleObject::class, 'class_var');        

        
        \LibCoverage\LibCoverage::End();
    }
}
class StaticReplacer_SimpleObject
{
    static $class_var;
}
