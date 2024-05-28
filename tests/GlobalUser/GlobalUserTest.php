<?php
namespace tests\DuckPhp\Component;

use DuckPhp\GlobalUser\GlobalUser;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        
        GlobalUser::ReplaceTo(MyGlobalUser::class);
        $post = array();
        $url_back ='';
        $ext = null;
        try{
        GlobalUser::_()->current();
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->getUsernames([]);
        }catch(\Exception $ex){}
        
        try{
        GlobalUser::_()->urlForRegist($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->urlForLogin($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->urlForLogout($url_back, $ext);
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->urlForHome($url_back, $ext);
        }catch(\Exception $ex){}   
        try{
        GlobalUser::_()->regist($post);
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->login($post);
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->logout($post);
        }catch(\Exception $ex){}
        
        
        GlobalUser::_(MyGlobalUser::_());
        
        GlobalUser::_()->id();
        
        
        try{
        GlobalUser::_()->action();
        }catch(\Exception $ex){}
        try{
        GlobalUser::_()->service();
        }catch(\Exception $ex){}
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyGlobalUser extends GlobalUser
{
    public function action()
    {
        return $this->proxy(MyGlobalUserAction::_());
    }
    public function service()
    {
        return $this->proxy(MyGlobalUserService::_());
    }
}
class MyGlobalUserAction
{
    //
}
class MyGlobalUserService
{
    //
}