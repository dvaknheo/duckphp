<?php
namespace tests\DuckPhp\Component;

use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\DuckPhp;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SimpleSingletonTrait;

class GlobalAdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdmin::class);
        DuckPhp::_()->init(['class_admin'=>MyAdmin::class]);
        Helper::Admin();

        var_dump(Helper::AdminId());
        var_dump(Helper::AdminName());
        Helper::Admin()->login([]);
        Helper::Admin()->logout();
        Helper::Admin()->checkAccess('class','method','url');
        Helper::Admin()->isSuper();
        
        Helper::Admin()->urlForLogin();
        Helper::Admin()->urlForLogout();
        Helper::Admin()->urlForHome();
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin extends GlobalAdmin
{
    public $actionClass = MyAdminAction::class;
    public $serviceClass = MyAdminService::class;
}
class MyAdminAction implements AdminActionInterface
{
    use SimpleSingletonTrait;
    public function id(){ echo 'id';return 'id';}
    public function name(){ echo  'name';return 'name';}
    public function login(array $post){ echo  __FUNCTION__;}
    public function logout(){ echo  __FUNCTION__;}
    public function checkAccess($class, string $method, ?string $url = null){ echo  __FUNCTION__;}
    public function isSuper(){ echo  __FUNCTION__;}
}
class MyAdminService implements AdminServiceInterface
{
    use SimpleSingletonTrait;
    public function urlForLogin($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function urlForLogout($url_back = null, $ext = null){ echo  __FUNCTION__;}
    public function urlForHome($url_back = null, $ext = null){ echo  __FUNCTION__;}
}