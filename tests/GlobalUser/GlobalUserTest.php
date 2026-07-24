<?php
namespace tests\DuckPhp\GlobalUser;

use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;

class GlobalUserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalUser::class);
        DuckPhp::_()->init(['class_user'=>MyUser::class]);
        Helper::UserId();
        try{
        (Helper::UserId());
        }catch(\Exception $ex){}
        try{
        (Helper::UserName());
        }catch(\Exception $ex){}
        try{
        Helper::User()->data(false);
        }catch(\Exception $ex){}
        
        Helper::User()->urlForHome();
        Helper::User()->urlForLogin();
        try{
        Helper::User()->urlForLogout();
        }catch(\Exception $ex){}
        try{
        Helper::User()->urlForRegist();
        }catch(\Exception $ex){}
        try{
        }catch(\Exception $ex){}
        
        Helper::User()->service();
        $data = [];
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        MyUser::_()->options['user_view_file_header']=$path.'views/block';
        Helper::User()->mergeViewData($data);
        Helper::User()->checkAccess('class','method','url');
        try{
        Helper::User()->log('a','b');
        }catch(\Throwable $ex){}
        
        
        $User = Helper::User()->batchGetUsernames([]);
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyUser extends GlobalUser
{
    public $options =[
        'user_url_home' => 'home',
        
        'user_callback_get_id' => [MyUserAction::class,'id'],
        'user_callback_url_login' => [MyUserAction::class,'urlForLogin'],
        'user_callback_get_service'=>[MyUserService::class,'_'],
        'user_view_file_header'=>'/abc',
    ];
}
class MyUserAction {
    use SingletonTrait;
    public function id(bool $check_login = true)
    {
        return 1;
    }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return 'abc';
    }
}
class MyUserService {
    use SingletonTrait;
    public function checkAccess($user_id, string $class, string $method, ?string $url = null)
    {
        return;
    }
    public function batchGetUsernames(array $ids): array
    {
        return [];
    }
    
}