<?php
namespace tests\DuckPhp\Ext{

use DuckPhp\Ext\CallableView;

class CallableViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(CallableView::class);
        
        $path_view=\MyCodeCoverage::GetClassTestPath(CallableView::class);
        $options=[
            'callable_view_head'=>'head',
            'callable_view_foot'=>'foot',
            'callable_view_class'=>null,
            'callable_view_prefix'=>'test_CallableView_',
            'callable_view_skip_replace'=>false,
            'path_view'=>$path_view,
        ];
        CallableView::G()->init($options, $context=null);
        
        $view="main";
        $data=["abc"=>"d"];
        
        CallableView::G()->_ShowBlock($view, $data);
        
        CallableView::G()->_Show($data , $view);
        CallableView::G()->_ShowBlock( 'block',$data);
        
        CallableView::G()->_Show( $data, 'view');
        
        \MyCodeCoverage::G()->end();
       
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