<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ViewHelper;

class ViewHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ViewHelper::class);
        
        $str="str";
        $a="a";$b="b";$c="c";
        ViewHelper::H($str);
    
        $path_base=realpath(__DIR__.'/../');
        $path_view=$path_base.'/data_for_tests/Helper/ViewHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::G()->init($options);
        ViewHelper::Display("view",['A'=>'b']);
        
        
        echo ViewHelper::L("a{b}c",['b'=>'123']);
        echo "---------------\n";
        echo ViewHelper::HL("&<{b}>",['b'=>'123']);
        echo ViewHelper::URL('xxxx');
        echo ViewHelper::Domain();
        
        \MyCodeCoverage::G()->end(ViewHelper::class);
        $this->assertTrue(true);

    }
}
