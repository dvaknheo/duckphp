<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\DuckPhp;
use DuckPhp\Helper\ControllerHelper;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Component\Pager;

class ControllerHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    static $x;
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ControllerHelperTrait::class);
        
        //code here
        //*
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_config'=>$path_config,
        ];
        \DuckPhp\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        ControllerHelper::Setting($key);
        ControllerHelper::Config($key, $file_basename);
        ControllerHelper::LoadConfig($file_basename);
        
        $str='<>';
        $url="";
        $method="method";
        ControllerHelper::H($str);
        ControllerHelper::Json(["a"=>"b"]);
        ControllerHelper::URL($url=null);
        ControllerHelper::Domain();
        ControllerHelper::Parameter('a','b');
        ControllerHelper::getRouteCallingMethod();
        ControllerHelper::DbCloseAll();
        //*/
        //*
        $path_base=realpath(__DIR__.'/../');
        $path_view=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::G()->init($options);
        ControllerHelper::Show(['A'=>'b'],"view");
        ControllerHelper::Render("view",['A'=>'b']);
        
        
        echo ControllerHelper::L("a{b}c",['b'=>'123']);
        echo "---------------\n";
        echo ControllerHelper::HL("&<{b}>",['b'=>'123']);
        echo ControllerHelper::URL('xxxx');
        
        $key="key";
        ControllerHelper::setViewHeadFoot($head_file=null, $foot_file=null);
        ControllerHelper::assignViewData($key, $value=null);
        ControllerHelper::getPathInfo();
        ControllerHelper::dumpAllRouteHooksAsString();
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";

        \DuckPhp\Core\App::system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        ControllerHelper::exit($code=0);
        
        var_dump("??????????");
        //*
        ControllerHelper::Exit404(false);
        ControllerHelper::ExitRedirect($url, false);
        ControllerHelper::ExitRedirectOutside($url, false);
        ControllerHelper::ExitRouteTo($url, false);
        ControllerHelper::ExitJson($ret,false);
        //*/
        
        ControllerHelper::header($output,$replace = true, $http_response_code=0);
        $key = "??";
        ControllerHelper::setcookie( $key,  $value = '',  $expire = 0,  $path = '/',  $domain = '',  $secure = false,  $httponly = false);
        
        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        ControllerHelper::assignExceptionHandler($classes, $callback);
        ControllerHelper::setMultiExceptionHandler($classes, $callback);
        ControllerHelper::setDefaultExceptionHandler($callback);
        


        ControllerHelper::GET('a');
        ControllerHelper::POST('a');
        ControllerHelper::REQUEST('a');
        ControllerHelper::COOKIE('a');
        ControllerHelper::SERVER('SCRIPT_FILENAME');
/////
echo"zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n\n";
DuckPhp::G()->init([]);
DuckPhp::Pager(new Pager);
var_dump(DuckPhp::Pager());
ControllerHelper::PageNo();
ControllerHelper::PageSize();
ControllerHelper::PageHtml(123);

        ControllerHelper::XpCall(function(){return "abc";});
        ControllerHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
            ControllerHelper::OnEvent("test",function(){});
            ControllerHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        ControllerHelper::IsAjax();
        ControllerHelper::CheckRunningController('self','static');


        \LibCoverage\LibCoverage::End();

        //*/
    }
}
