<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\GlobalAdmin;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        
        GlobalAdmin::_();
        $post = array();
        $url_back ='';
        $ext = null;
        try{
        GlobalAdmin::_()->urlForRegist($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalAdmin::_()->urlForLogin($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalAdmin::_()->urlForLogout($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalAdmin::_()->urlForHome($url_back, $ext);
        }catch(\Exception $ex){}   
        try{
        GlobalAdmin::_()->regist($post);
        }catch(\Exception $ex){}
        try{
        GlobalAdmin::_()->login($post);
        }catch(\Exception $ex){}
        try{
        GlobalAdmin::_()->logout($post);
        }catch(\Exception $ex){}
        
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyGlobalAdmin extends GlobalAdmin
{
    public function __construct(){}
}
