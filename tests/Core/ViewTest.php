<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\View;
use DuckPhp\Core\App;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(View::class);
        
        
        $path_view=\LibCoverage\LibCoverage::G()->getClassTestPath(View::class);
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::_()->init($options,new App());
        View::_()->setViewHeadFoot('head', 'foot');
        View::_()->assignViewData('A','aa');
        View::_()->assignViewData(['B'=>'bb','C'=>'cc']);
        View::Show(['D'=>'ddddddd'],"view");
        View::_()->setViewHeadFoot(null, null);
        View::Show(['D'=>'ddddddd'],"view");
        
        View::Display("block",['A'=>'b']);
        View::Render("block",['A'=>'b']);
        View::_()->getViewData();
        //View::_()->getViewPath();
        View::_()->setViewHeadFoot(null,null);
        
        View::_()->isInited();
        $options=[
            'path'=>$path_view,
            'path_view'=>'',
        ];

        View::_()->init($options);
        View::_()->reset();
        ////
        //View::_()->options['path_view_override_from']=$path_view.'overrided/';
        //View::_()->Show([],'override');
        \LibCoverage\LibCoverage::End();
    }
}

