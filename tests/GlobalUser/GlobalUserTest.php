<?php
namespace tests\DuckPhp\GlobalUser;

use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SimpleSingletonTrait;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        DuckPhp::_()->init(['class_user'=>MyUser::class]);
        try{
        Helper::User();
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::UserId());
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::UserName());
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::UserService());
        }catch(\Exception $ex){}
        try{
        Helper::User()->login([]);
        }catch(\Exception $ex){}
        try{
        Helper::User()->logout();
        }catch(\Exception $ex){}
        try{
        Helper::User()->regist([]);

        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForLogin();
        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForLogout();
        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForHome();
        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForRegist();
        }catch(\Exception $ex){}
        try{
        Helper::User()->batchGetUsernames([]);
        }catch(\Exception $ex){}
        Helper::User()->checkAccess('class','method',null);
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyUser extends GlobalUser
{

}
