<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(View::class);
        
        
        $path_view=\GetClassTestPath(View::class);
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::G()->init($options);
        View::G()->setViewWrapper('head', 'foot');
        View::G()->assignViewData('A','aa');
        View::G()->assignViewData(['B'=>'bb','C'=>'cc']);

        View::Show(['D'=>'ddddddd'],"view");
        
        View::Display("block",['A'=>'b']);

        View::G()->setOverridePath($path_view.'overrided/');
        View::G()->setViewWrapper(null,null);
        View::G()->_Show(['A'=>'b'],"override");
        
        $options=[
            'path'=>$path_view,
            'path_view'=>'',
        ];
        \DuckPhp\Core\View::G()->init($options);
        
        \MyCodeCoverage::G()->end(View::class);
        $this->assertTrue(true);
    }
}
