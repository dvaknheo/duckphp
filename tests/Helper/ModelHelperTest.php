<?php
namespace tests\DNMVCS\Helper;

use DNMVCS\Helper\ModelHelper;

class ModelHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ModelHelper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ModelHelper::class);
        $this->assertTrue(true);
        /*
        ModelHelper::G()->DB($tag=null);
        ModelHelper::G()->DB_W();
        ModelHelper::G()->DB_R();
        //*/
    }
}
