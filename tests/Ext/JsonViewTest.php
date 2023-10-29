<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\JsonView;
use DuckPhp\DuckPhp;
use DuckPhp\Core\SystemWrapper;


class JsonViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(JsonView::class);

        $options=[
            'json_view_skip_replace'=> false,
            'json_view_skip_vars'=> ['head'],
        ];
        SystemWrapper::_()->_system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        JsonView::G()->init($options, DuckPhp::G());
        
        $view="main";
        $data=["abc"=>"d"];
        
        JsonView::G()->_Display($view, $data);
        
        JsonView::G()->_Show($data , $view);
        
        \LibCoverage\LibCoverage::End();
       
    }
}
