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
        
        $key='key';
        $file_basename='config';
        ControllerHelper::Setting($key);
        try{

        ControllerHelper::Config($file_basename, $key, null);
        }catch(\Exception $ex){}
        
        ControllerHelper::Parameter('a','b');
        ControllerHelper::getRouteCallingMethod();
        ControllerHelper::getRouteCallingClass();
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
        
        
        $key="key";
        ControllerHelper::setViewHeadFoot($head_file=null, $foot_file=null);
        ControllerHelper::assignViewData($key, $value=null);
        ControllerHelper::PathInfo();
        ControllerHelper::Domain();
        
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
ControllerHelper::PageWindow();
ControllerHelper::PageHtml(123);

        ControllerHelper::XpCall(function(){return "abc";});
        ControllerHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
            ControllerHelper::OnEvent("test",function(){});
            ControllerHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        ControllerHelper::IsAjax();

        try{
            //ControllerHelper::ThrowOn(true,"just a exception");
        }catch(\Exception $ex){
        }
        \LibCoverage\LibCoverage::End();

        //*/
    }
}
