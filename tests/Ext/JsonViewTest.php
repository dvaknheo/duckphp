<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\JsonView;
use DuckPhp\DuckPhp;

class JsonViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(JsonView::class);

        $options=[
            'json_view_skip_replace'=> false,
        ];
        DuckPhp::G()->system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        JsonView::G()->init($options, DuckPhp::G());
        
        $view="main";
        $data=["abc"=>"d"];
        
        JsonView::G()->_Display($view, $data);
        
        JsonView::G()->_Show($data , $view);
        
        \MyCodeCoverage::G()->end();
       
    }
}