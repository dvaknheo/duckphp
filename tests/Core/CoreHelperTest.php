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
        CoreHelper::ShowJson($ret);
        
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
        
        $options = ['is_debug'=>true, 
            'html_handler'=>function(&$str){return "<".$str.">";},
            'lang_handler'=> function($str,$args=[]){ return "lang:".$str.";";}
        ];
        App::_(new App())->init($options);

        echo CoreHelper::Hl("abc", []);

        CoreHelper::PhaseCall('z',function(){echo App::Phase();},123);
        CoreHelper::PhaseCall('',function(){echo App::Phase();},123);
        
        \LibCoverage\LibCoverage::End();

    }
}
