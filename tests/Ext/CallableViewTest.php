<?php
namespace tests\DNMVCS\Ext{

use DNMVCS\Ext\CallableView;

class CallableViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(CallableView::class);
        $options=[
            'callable_view_head'=>'head',
            'callable_view_foot'=>'foot',
            'callable_view_class'=>null,
            'callable_view_prefix'=>'test_CallableView_',
            'callable_view_skip_replace'=>false,
        ];
        CallableView::G()->init($options, $context=null);
        
        $view="main";
        $data=["abc"=>"d"];
        
        CallableView::G()->_ShowBlock($view, $data);
        
        CallableView::G()->_Show($data , $view);
        
        
        \MyCodeCoverage::G()->end(CallableView::class);
        $this->assertTrue(true);
       
    }
}
}
namespace{

function test_CallableView_main($data)
{
    //
}
function test_CallableView_head($data)
{
    //
}
function test_CallableView_foot($data)
{
    //
}
}