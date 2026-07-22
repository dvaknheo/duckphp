<?php
namespace tests\DuckPhp\GlobalUser;

use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;
use DuckPhp\Core\ComponentBase;

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
        Helper::User()->batchGetUsernames([]);

        try{
        Helper::User()->checkAccess('class','method',null);
        }catch(\Exception $ex){}
        Helper::User()->on('MyEvent!',function($a,$b,$c){
            //var_dump($a,$b,$c);
        });
        Helper::User()->fire('MyEvent!',1,2,3);
        Helper::User()->service();
        Helper::User()->self()->flag = false;
        try{
        Helper::User()->service();
        }catch(\Exception $ex){}
        try{
        (new GlobalUser())->localService();
        }catch(\Exception $ex){}
        try{
        (new GlobalUser())->id();
        }catch(\Exception $ex){}
        ///////////////
        $input=['header'=>'x'];
        Helper::User()->self()->flag = true;

        Helper::User()->getHeaderFooterData($input);
        Helper::User()->mergeView($input,'header','footer');
        Helper::User()->log('fsafsa','default');
        \LibCoverage\LibCoverage::End();
    }
}
class MyUser extends GlobalUser
{
    public $flag = true;
    public function localService()
    {
        return $this->flag ? MyUserService::_():null;
    }
    public function id($checkLogin =true):int
    {
        return 1;
    }
}
class MyUserService implements UserServiceInterface
{
    use SingletonTrait;
    public function doBatchGetUsernames(array $ids): array
    {
        return [];
    }
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        return;
    }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url =null): void
    {
        return;
    }
}
