<?php
namespace tests\DuckPhp\Ext{

use DuckPhp\Ext\CallableView;

class CallableViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CallableView::class);
        
        $path_view=\LibCoverage\LibCoverage::G()->getClassTestPath(CallableView::class);
        $options=[
            'callable_view_head'=>'head',
            'callable_view_foot'=>'foot',
            'callable_view_class'=>null,
            'callable_view_prefix'=>'test_CallableView_',
            'callable_view_skip_replace'=>false,
            'path_view'=>$path_view,
        ];
        CallableView::_()->init($options, $context=null);
        
        $view="main";
        $data=["abc"=>"d"];
        
        CallableView::_()->_Display($view, $data);
        
        CallableView::_()->_Show($data , $view);
        CallableView::_()->_Display( 'block',$data);
        
        CallableView::_()->_Show( $data, 'view');
        
        \LibCoverage\LibCoverage::End();
       
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