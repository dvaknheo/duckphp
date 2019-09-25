<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\Pager;

class PagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Pager::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Pager::class);
        $this->assertTrue(true);
        /*
        Pager::G()->SG();
        Pager::G()->_SG();
        Pager::G()->Current();
        Pager::G()->Render($total, $options=[]);
        Pager::G()->_current();
        Pager::G()->init($options=[], $context=null);
        Pager::G()->getUrl($page);
        Pager::G()->defaultGetUrl($page);
        Pager::G()->_render($total, $options=[]);
        //*/
    }
}
