<?php
namespace tests\DuckPhp\Component;

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
        Helper::User();

        var_dump(Helper::UserId());
        var_dump(Helper::UserName());
        Helper::User()->login([]);
        Helper::User()->logout();
        Helper::User()->regist([]);

        
        Helper::User()->urlForLogin();
        Helper::User()->urlForLogout();
        Helper::User()->urlForHome();
        Helper::User()->urlForRegist();
        Helper::User()->getUsernames([]);
        
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyUser extends GlobalUser
{
    public $actionClass = MyUserAction::class;
    public $serviceClass = MyUserService::class;
}
class MyUserAction implements UserActionInterface
{
    use SimpleSingletonTrait;
    public function id(){ echo 'id';return 'id';}
    public function name(){ echo  'name';return 'name';}
    public function login(array $post){ echo  __FUNCTION__;}
    public function logout(){ echo  __FUNCTION__;}
    public function regist(array $post){ echo  __FUNCTION__;}

}
class MyUserService implements UserServiceInterface
{
    use SimpleSingletonTrait;
    public function urlForLogin($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function urlForLogout($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function urlForHome($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function urlForRegist($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function getUsernames(array $ids){ echo  __FUNCTION__;}
}