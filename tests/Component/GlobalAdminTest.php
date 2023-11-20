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
        GlobalAdmin::_()->checkLogin();
        }catch(\Exception $ex){}
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
        
        
        GlobalAdmin::_(MyGlobalAdmin::_());
        GlobalAdmin::_()->current();
        GlobalAdmin::_()->id();
        GlobalAdmin::_()->data();
        GlobalAdmin::_()->isSuper();
        GlobalAdmin::_()->canAccessCurrent();
        GlobalAdmin::_()->canAccessUrl('/a_path');
        GlobalAdmin::_()->canAccessCall('Class','method');
        
        GlobalAdmin::CallInPhase(GlobalAdmin::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyGlobalAdmin extends GlobalAdmin
{
    public function checkLogin()
    {
        return true;
    }
}
