<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\EmptyView;

class EmptyViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(EmptyView::class);

        $options=[
            'empty_view_key_view'=> 'view',
            'empty_view_key_skip_head_foot'=> 'skip_head_foot',
            'empty_view_view_wellcome'=> 'Main/',
            'empty_view_trim_view_wellcome'=> true,
            'empty_view_skip_replace'=> false,
        ];
        EmptyView::_()->init($options, $context=null);
        
        $view="main";
        $data=["abc"=>"d"];
        
        EmptyView::_()->_Display($view, $data);
        
        EmptyView::_()->_Show($data , $view);
        EmptyView::_()->_Display( 'block',$data);
        
        EmptyView::_()->_Show( $data, 'view');
        EmptyView::_()->_Show( $data, 'Main/');
        
        \LibCoverage\LibCoverage::End();
       
    }
}
