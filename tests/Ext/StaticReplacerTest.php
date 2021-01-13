<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Ext\StaticReplacer;

class StaticReplacerTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StaticReplacer::class);
        
        //code here        
        $k="k";$v="v";
        StaticReplacer::G()->_GLOBALS($k, $v=null);
        StaticReplacer::G()->_STATICS($k, $v=null);
        StaticReplacer::G()->_CLASS_STATICS(StaticReplacer_SimpleObject::class, 'class_var');        

        
        \MyCodeCoverage::G()->end();
    }
}
class StaticReplacer_SimpleObject
{
    static $class_var;
}
