<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(View::class);
        
        $path_base=realpath(__DIR__.'/../');
        $path_view=$path_base.'/data_for_tests/Core/View/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DNMVCS\Core\View::G()->init($options);
        View::G()->_Show(['A'=>'b'],"view");
        View::G()->_ShowBlock("view",['A'=>'b']);
        
        $key="key";
        View::G()->setViewWrapper($head_file=null, $foot_file=null);
        View::G()->assignViewData($key, $value=null);
        
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
