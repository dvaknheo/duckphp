<?php
namespace tests\DuckPhp\Ext{

use DuckPhp\Ext\CallableView;
use DuckPhp\Core\SingletonExTrait;
class CallableViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CallableView::class);
        
        $path_view=\LibCoverage\LibCoverage::G()->getClassTestPath(CallableView::class);
        $options=[
            'callable_view_header'=>'header',
            'callable_view_footer'=>'footer',
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
        $options=[
            'x'=>'zz',
            'callable_view_class'=>MyViewClass::class,
            'callable_view_is_object_call'=>true,
        ];
        MyViewClass::_(MyViewClass2::_());
        CallableView::_(new CallableView())->init($options);
        CallableView::_()->_Show($data, 'main');
        \LibCoverage\LibCoverage::End();
       
    }
}
class MyViewClass
{
    use SingletonExTrait;
public function main(){var_dump("hit?");}
}
class MyViewClass2 extends MyViewClass
{
    public function main(){var_dump("hit!");}
}
}
namespace{

function test_CallableView_main($data)
{
    //
}
function test_CallableView_header($data)
{
    //
}
function test_CallableView_footer($data)
{
    //
}
}