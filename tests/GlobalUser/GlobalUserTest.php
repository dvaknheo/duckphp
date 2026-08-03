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
        // mergeViewData: without header
        unset(MyUser::_()->options['user_view_file_header']);
        $res = Helper::User()->mergeViewData([]);
        \PHPUnit\Framework\Assert::assertSame('', $res['__view_data']['header'] ?? '');
        // mergeViewData: with header
        MyUser::_()->options['user_view_file_header']=$path.'views/block';
        Helper::User()->mergeViewData($data);
        // mergeViewData: with header + footer
        MyUser::_()->options['user_view_file_footer']=$path.'views/block';
        $data3 = Helper::User()->mergeViewData($data);
        \PHPUnit\Framework\Assert::assertStringContainsString('Block', $data3['__view_data']['footer'] ?? '');
        // test user_callback_for_add_ext_view_data
        MyUser::_()->options['user_callback_for_add_ext_view_data'] = [MyUserAction::class, 'myAddExtViewData'];
        $data2 = Helper::User()->addExtViewData([]);
        \PHPUnit\Framework\Assert::assertTrue(isset($data2['__view_data']['custom']));
        Helper::User()->checkAccess('class','method','url');
        // checkAccess() 无参分支：获取路由上下文
        Helper::User()->checkAccess();
        try{
        Helper::User()->log('a','b');
        }catch(\Throwable $ex){}
        
        // show() 分支：渲染视图
        ob_start();
        Helper::User()->show([], $path.'views/block');
        ob_get_clean();
        
        
        $User = Helper::User()->batchGetUsernames([]);
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyUser extends GlobalUser
{
    public $options =[
        'user_url_home' => 'home',
        
        'user_callback_for_id' => [MyUserAction::class,'id'],
        'user_callback_for_name' => [MyUserAction::class,'name'],
        'user_url_logout' => 'logout',

        'user_callback_for_url_for_login' => [MyUserAction::class,'urlForLogin'],
        'user_callback_for_local_service'=>[MyUserService::class,'_'],
        'user_view_file_header'=>'/abc',
    ];
}
class MyUserAction {
    use SingletonTrait;
    public function id(bool $check_login = true)
    {
        return 1;
    }
    public function name(bool $check_login = true):string
    {
        return "test_user";
    }

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
    {
        return 'abc';
    }
    public function myAddExtViewData(array $data): array
    {
        $data['__view_data']['custom'] = true;
        return $data;
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