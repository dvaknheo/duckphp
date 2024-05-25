<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\App;
use DuckPhp\Core\CoreHelper;

class CoreHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CoreHelper::class);

        $str ="<b>{abc}</b>";
        $args = ["abc"=>"def"];
        $data = ["abc"=>"def"];
        $var = $data;
        $message = $str; $context =$data;
        
        echo CoreHelper::L($str, $args);
        echo CoreHelper::L($str);
        echo CoreHelper::Hl($str, $args);
        echo CoreHelper::Json($data);
        echo CoreHelper::H($str);
        
        echo CoreHelper::H($str);
        echo CoreHelper::H($str);
        
        
        $t = [$str,"zz"];
        CoreHelper::H($t);
        $t = 123;
        echo CoreHelper::H($t);
        
        CoreHelper::TraceDump();
        CoreHelper::VarLog($var);
        CoreHelper::DebugLog($message, $context);
        CoreHelper::var_dump($args);
        CoreHelper::Logger();
        
        $sql = "select * from users";
        echo CoreHelper::_()->_SqlForPager($sql, 1, 10);
        echo CoreHelper::_()->_SqlForCountSimply($sql);
        
        $options = ['is_debug'=>true];
        App::_(new App())->init($options);

        CoreHelper::_()->_TraceDump();
        CoreHelper::_()->_VarLog($var);
        CoreHelper::_()->_DebugLog($message, $context);
        CoreHelper::_()->_var_dump($args);

        CoreHelper::IsDebug();
        CoreHelper::IsRealDebug();
        CoreHelper::Platform();
        
        \DuckPhp\Core\SystemWrapper::_()->_system_wrapper_replace(['exit'=>function($code=0){
            var_dump(DATE(DATE_ATOM));
        }]);

        $url="/test";
        CoreHelper::IsAjax();
        
        CoreHelper::Show302(CoreHelper::Url($url));
        CoreHelper::Show302('https://www.baidu.com/');
        CoreHelper::Show404();
        CoreHelper::_()->options['is_debug']=true;
        CoreHelper::ShowJson(['date'=>DATE(DATE_ATOM)]);

        CoreHelper::XpCall(function(){return "abc";});
        CoreHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        echo CoreHelper::Res();
        echo CoreHelper::Domain();
        try{
        echo CoreHelper::Display('no_exits',[]);
        }catch(\Throwable $ex){}
        echo CoreHelper::Json($data);

        $sql="Select * from users";
        CoreHelper::SqlForPager($sql,1,5);
        CoreHelper::SqlForCountSimply($sql);   
        
        try{
            CoreHelper::BusinessThrowOn(false, "haha",1);
            CoreHelper::BusinessThrowOn(true, "haha",2,\Exception::class);
        }catch(\Throwable $ex){}
        try{
            CoreHelper::ControllerThrowOn(false, "haha",1);
            CoreHelper::ControllerThrowOn(true, "haha",2,\Exception::class);
        }catch(\Throwable $ex){}
        
        echo CoreHelper::Json($data);
        CoreHelper::PathOfProject();
        CoreHelper::PathOfRuntime();
        
        
        $options = ['is_debug'=>true, 
            'html_handler'=>function(&$str){return "<".$str.">";},
            'lang_handler'=> function($str,$args=[]){ return "lang:".$str.";";},
            'app' => [
                SubCoreHelperApp1::class =>[
                    'namespace' => __NAMESPACE__ ,
                ],
            ],
        ];
        
        MaiCoreHelperApp::_(new MaiCoreHelperApp())->init($options);


        CoreHelper::PhaseCall('z',function(){echo MaiCoreHelperApp::Phase();},123);
        CoreHelper::PhaseCall('',function(){echo MaiCoreHelperApp::Phase();},123);
        
        
        
        ////[[[[
        $str ="<b>{abc}</b>";
        $args = ["abc"=>"def"];
        echo CoreHelper::_()->_Hl($str, $args);
        echo CoreHelper::_()->formatString($str, $args);
        $ext =[];
        CoreHelper::_()->recursiveApps($ext,function($class,&$ext){return $ext;});


        $data = CoreHelper::_()->getAllAppClass();
        //*
        MaiCoreHelperApp::_()->options['html_handler']=null;
        MaiCoreHelperApp::_()->options['lang_handler']=null;


        
        echo CoreHelper::_()->getAppClassByComponent(CoreHelperComponent::class);
        echo "\n";
        echo CoreHelper::_()->getAppClassByComponent('NoExits');
        echo "\n";
            echo CoreHelper::_()->regExtCommandClass(CoreHelperCommand::class);

        //*/
        ////]]]]
        \LibCoverage\LibCoverage::End();

    }
}
class MaiCoreHelperApp extends App
{
    public $options=[
            'namespace' =>'no_MaiCoreHelperApp',
    ];
    public function onInit()
    {
        SubCoreHelperApp1::_(SubCoreHelperApp2::_());
    }
}
class SubCoreHelperApp1 extends App
{
}
class SubCoreHelperApp2 extends App
{
}
class CoreHelperComponent
{
    //
}
class CoreHelperCommand
{
    //
}
