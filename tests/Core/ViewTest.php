<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(View::class);
        
        View::G(new View());
        $options=[
            'path'=>'',
            'path_view'=>'',
        ];
        View::G()->init($options);
        
        \MyCodeCoverage::G()->end(View::class);
        $this->assertTrue(true);
        /*
        View::G()->_Show($data=[], $view);
        View::G()->_ShowBlock($view, $data=null);
        View::G()->prepareFiles();

        View::G()->setViewWrapper($head_file, $foot_file);
        View::G()->assignViewData($key, $value=null);
        //*/
    }
}
