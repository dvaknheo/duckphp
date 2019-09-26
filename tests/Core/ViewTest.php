<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(View::class);
        
        
        $path_view=\GetClassTestPath(View::class);
        $options=[
            'path_view'=>$path_view,
        ];
        \DNMVCS\Core\View::G()->init($options);
        View::G()->setViewWrapper('head', 'foot');
        View::G()->assignViewData('A','aa');
        View::G()->assignViewData(['B'=>'bb','C'=>'cc']);

        View::G()->_Show(['D'=>'ddddddd'],"view");
        
        View::G()->_ShowBlock("block",['A'=>'b']);
        
        $options=[
            'path'=>$path_view,
            'path_view'=>'',
        ];
        \DNMVCS\Core\View::G()->init($options);
        
        \MyCodeCoverage::G()->end(View::class);
        $this->assertTrue(true);
        /*
        View::G()->_Show($data=[], $view);
        View::G()->_ShowBlock($view, $data=null);

        View::G()->setViewWrapper($head_file, $foot_file);
        View::G()->assignViewData($key, $value=null);
        //*/
    }
}
