<?php
namespace tests\DuckPhp\GlobalAdmin;

use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        DuckPhp::_()->init(['class_admin'=>MyAdmin::class]);
        Helper::AdminId();
        try{
        (Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        (Helper::AdminName());
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->data(false);
        }catch(\Exception $ex){}
        
        Helper::Admin()->urlForHome();
        Helper::Admin()->urlForLogin();
        try{
        Helper::Admin()->urlForLogout();
        }catch(\Exception $ex){}
        try{
        }catch(\Exception $ex){}
        
        Helper::Admin()->service();
        $data = [];
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        MyAdmin::_()->options['admin_view_file_header']=$path.'views/block';
        Helper::Admin()->mergeViewData($data);
        Helper::Admin()->checkAccess('class','method','url');
        try{
        Helper::Admin()->log('a','b');
        }catch(\Throwable $ex){}
        
        
        $admin = Helper::Admin();
        try{
        $admin->isSuper();
        }catch(\Throwable $ex){}
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin extends GlobalAdmin
{
    public $options =[
        'admin_url_home' => 'home',
        
        'admin_callback_get_id' => [MyAction::class,'id'],
        'admin_callback_url_login' => [MyAction::class,'urlForLogin'],
        'admin_callback_get_service'=>[MyService::class,'_'],
        'admin_view_file_header'=>'/abc',
    ];
}
class MyAction {
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
class MyService {
    use SingletonTrait;
    public function checkAccess($admin_id, string $class, string $method, ?string $url = null)
    {
        return;
    }
    
}