<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Controller\ControllerHelper as Helper;
use DuckPhp\Component\Pager;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    static $x;
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);
        
        //code here
        //*
        
        $key='key';
        $file_basename='config';
        Helper::Setting($key);
        try{

        Helper::Config($file_basename, $key, null);
        }catch(\Exception $ex){}
        
        Helper::Parameter('a','b');
        Helper::getRouteCallingMethod();
        Helper::getRouteCallingClass();
        //*/
        //*
        $path_base=realpath(__DIR__.'/../../');
        $path_view=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::_()->init($options);
        Helper::Show(['A'=>'b'],"view");
        Helper::Render("view",['A'=>'b']);
        
        
        $key="key";
        Helper::setViewHeadFoot($head_file=null, $foot_file=null);
        Helper::assignViewData($key, $value=null);
        Helper::PathInfo();
        Helper::Domain();
        Helper::Url('def/g');
        Helper::Res('ab/c');
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";

        \DuckPhp\Core\SystemWrapper::system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        Helper::exit($code=0);
        
        var_dump("??????????");
        //*
        Helper::Show404();
        Helper::Show302($url);
        Helper::ShowJson($ret);
        //*/
        
        Helper::header($output,$replace = true, $http_response_code=0);
        $key = "??";
        Helper::setcookie( $key,  $value = '',  $expire = 0,  $path = '/',  $domain = '',  $secure = false,  $httponly = false);
        
        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        Helper::assignExceptionHandler($classes, $callback);
        Helper::setMultiExceptionHandler($classes, $callback);
        Helper::setDefaultExceptionHandler($callback);
        

        Helper::IsPost();
        Helper::GET('a');
        Helper::POST('a');
        Helper::REQUEST('a');
        Helper::COOKIE('a');
        Helper::SERVER('SCRIPT_FILENAME');
/////
echo"zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n\n";
DuckPhp::_()->init(['my_test_key' => 'my_value']);
Helper::AppOptions('my_test_key');
Helper::AppOptions('my_test_key', 'default');
Helper::AppOptions('no_such_key', 'default');
Helper::Pager(new Pager());
Helper::PageNo();
Helper::PageWindow();
Helper::PageHtml(123);

        Helper::XpCall(function(){return "abc";});
        Helper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
            Helper::OnGlobalEvent("test",function(){});
            Helper::FireGlobalEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        Helper::IsAjax();

        try{
            Helper::ControllerThrowOn(false, "haha",1);
            Helper::ThrowOn(false, "haha",1);
        }catch(\Throwable $ex){}
        
        try{
            Helper::Admin();
        }catch(\Throwable $ex){
        }
        try{
            Helper::AdminId();
        }catch(\Throwable $ex){
        }
        try{
            Helper::AdminName();
        }catch(\Throwable $ex){
        }
        try{
            Helper::User();
        }catch(\Throwable $ex){
        }
        try{
            Helper::UserId();
        }catch(\Throwable $ex){
        }
        try{
            Helper::UserName();
        }catch(\Throwable $ex){
            
        }
        try{
            Helper::AdminService();
        }catch(\Throwable $ex){}
        try{
            Helper::UserService();
        }catch(\Throwable $ex){}
            
        \DuckPhp\Core\App::_()->options['installed'] = true;
        try{
            Helper::checkInstall("install");
        }catch(\Throwable $ex){}
        
            
        \LibCoverage\LibCoverage::End();
    }
}
