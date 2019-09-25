<?php
namespace tests\DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\ViewHelper;

class ViewHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ViewHelper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ViewHelper::class);
        $this->assertTrue(true);
        /*
        ViewHelper::G()->H($str);
        ViewHelper::G()->ShowBlock($view, $data=null);
        ViewHelper::G()->DumpTrace();
        ViewHelper::G()->Dump(...$args);
        //*/
    }
}
