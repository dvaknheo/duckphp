<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\DuckPhp;
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
        //*/
        //*
        $path_base=realpath(__DIR__.'/../');
        $path_view=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_view'=>$path_view,
        ];
        \DuckPhp\Core\View::_()->init($options);
        ControllerHelper::Show(['A'=>'b'],"view");
        ControllerHelper::Render("view",['A'=>'b']);
        
        
        $key="key";
        ControllerHelper::setViewHeadFoot($head_file=null, $foot_file=null);
        ControllerHelper::assignViewData($key, $value=null);
        ControllerHelper::PathInfo();
        ControllerHelper::Domain();
        ControllerHelper::Url('def/g');
        ControllerHelper::Res('ab/c');
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";

        \DuckPhp\Core\SystemWrapper::system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        ControllerHelper::exit($code=0);
        
        var_dump("??????????");
        //*
        ControllerHelper::Show404();
        ControllerHelper::Show302($url);
        ControllerHelper::ShowJson($ret);
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
DuckPhp::_()->init([]);
ControllerHelper::Pager(new Pager());
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
            ControllerHelper::ControllerThrowOn(false, "haha",1);
        }catch(\Throwable $ex){}
        
        try{
            ControllerHelper::Admin();
        }catch(\Throwable $ex){
        }
        try{
            ControllerHelper::AdminId();
        }catch(\Throwable $ex){
        }
        try{
            ControllerHelper::AdminName();
        }catch(\Throwable $ex){
        }
        try{
            ControllerHelper::User();
        }catch(\Throwable $ex){
        }
        try{
            ControllerHelper::UserId();
        }catch(\Throwable $ex){
        }
        try{
            ControllerHelper::UserName();
        }catch(\Throwable $ex){
            
        }
        try{
            ControllerHelper::AdminAction();
        }catch(\Throwable $ex){}
        try{
            ControllerHelper::UserAction();
        }catch(\Throwable $ex){}
        try{
            ControllerHelper::AdminService();
        }catch(\Throwable $ex){}
        try{
            ControllerHelper::UserService();
        }catch(\Throwable $ex){}
        \LibCoverage\LibCoverage::End();
    }
}
class ControllerHelper
{
    use ControllerHelperTrait;
}