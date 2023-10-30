<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\Helper;
use DuckPhp\Core\App;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);

        $str ="<b>{abc}</b>";
        $args = ["abc"=>"def"];
        $data = ["abc"=>"def"];
        $var = $data;
        $message = $str; $context =$data;
        Helper::_()->_IsAjax();
        echo Helper::L($str, $args);
        echo Helper::L($str);
        echo Helper::Hl($str, $args);
        echo Helper::Json($data);
        echo Helper::H($str);
        
        Helper::XpCall(function(){return "abc";});
        Helper::XpCall(function(){ throw new \Exception('ex'); });
        
        $t = [$str,"zz"];
        Helper::H($t);
        $t = 123;
        echo Helper::H($t);
        
        Helper::TraceDump();
        Helper::VarLog($var);
        Helper::DebugLog($message, $context);
        Helper::var_dump($args);
        Helper::Logger();
        
        $sql = "select * from users";
        echo Helper::_()->_SqlForPager($sql, 1, 10);
        echo Helper::_()->_SqlForCountSimply($sql);
        
        $options = ['is_debug'=>true];
        App::G(new App())->init($options);
        
        Helper::_()->_TraceDump();
        Helper::_()->_VarLog($var);
        Helper::_()->_DebugLog($message, $context);
        Helper::_()->_var_dump($args);
        
        
        \DuckPhp\Core\SystemWrapper::_()->_system_wrapper_replace(['exit'=>function($code=0){
            var_dump(DATE(DATE_ATOM));
        }]);

        $url="/test";
        
        Helper::ExitRedirect($url);
        Helper::ExitRedirect('http://www.github.com');

        Helper::ExitRedirectOutside("http://www.github.com",true);
        Helper::ExitRouteTo($url);
        Helper::Exit404();
        Helper::G()->options['is_debug']=true;
        Helper::ExitJson($ret);
        
         echo Helper::Json($data);
////////////////
        \LibCoverage\LibCoverage::End();

    }
}
