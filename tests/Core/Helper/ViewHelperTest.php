<?php
namespace tests\DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\ViewHelper;

class ViewHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ViewHelper::class);
        
        $str="str";
        $a="a";$b="b";$c="c";
        ViewHelper::H($str);
        ViewHelper::DumpTrace();
        ViewHelper::Dump($a,$b,$c);
    
        $path_base=realpath(__DIR__.'/../../');
        $path_view=$path_base.'/data_for_tests/Core/Helper/ViewHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DNMVCS\Core\View::G()->init($options);
        ViewHelper::ShowBlock("view",['A'=>'b']);
        
        \MyCodeCoverage::G()->end(ViewHelper::class);
        $this->assertTrue(true);
        /*
        
        ViewHelper::ShowBlock($view, $data=null);
        
        ViewHelper::Dump(...$args);
        //*/
    }
}
