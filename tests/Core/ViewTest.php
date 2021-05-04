<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(View::class);
        
        
        $path_view=\LibCoverage\LibCoverage::G()->getClassTestPath(View::class);
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::G()->init($options);
        View::G()->setViewHeadFoot('head', 'foot');
        View::G()->assignViewData('A','aa');
        View::G()->assignViewData(['B'=>'bb','C'=>'cc']);

        View::Show(['D'=>'ddddddd'],"view");
        
        View::Display("block",['A'=>'b']);
        View::Render("block",['A'=>'b']);
        View::G()->getViewData();

        View::G()->getViewPath();
        View::G()->setViewHeadFoot(null,null);
                View::G()->options['path_view_override']=$path_view.'overrided/';
        View::G()->_Show(['A'=>'b'],"override");
        View::G()->isInited();
        $options=[
            'path'=>$path_view,
            'path_view'=>'',
        ];
        View::G()->init($options);
        View::G()->reset();
        
        \LibCoverage\LibCoverage::End();
    }
}
