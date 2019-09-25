<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\CallableView;

class CallableViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(CallableView::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(CallableView::class);
        $this->assertTrue(true);
        /*
        CallableView::G()->init($options=[], $context=null);
        CallableView::G()->viewToCallback($func);
        CallableView::G()->_Show($data = [], $view);
        CallableView::G()->_ShowBlock($view, $data = null);
        //*/
    }
}
