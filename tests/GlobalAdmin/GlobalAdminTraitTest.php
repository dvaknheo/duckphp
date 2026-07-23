<?php
namespace tests\DuckPhp\GlobalAdmin;

use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalAdmin\GlobalAdminTrait;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;
use DuckPhp\GlobalAdmin\AdminException;
use DuckPhp\DuckPhp;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Foundation\Helper;
use DuckPhp\Foundation\SingletonTrait;

class GlobalAdminTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(GlobalAdminTrait::class);
        DuckPhp::_()->init(['class_admin'=>MyAdmin::class]);
        //////////////////////
        try{
        Helper::Admin()->checkAccess('class','method',null);
        }catch(\Exception $ex){}
        Helper::Admin()->on('MyEvent!',function($a,$b,$c){
            //var_dump($a,$b,$c);
        });
        Helper::Admin()->fire('MyEvent!',1,2,3);
        Helper::Admin()->service();
        Helper::Admin()->self()->flag = false;
        try{
        Helper::Admin()->service();
        }catch(\Exception $ex){}
        try{
        (new GlobalAdmin())->localService();
        }catch(\Exception $ex){}
        try{
        (new GlobalAdmin())->id();
        }catch(\Exception $ex){}
        ///////////////
        $input=['header'=>'x'];
        Helper::Admin()->self()->flag = true;

        Helper::Admin()->getHeaderFooterData($input);
        Helper::Admin()->mergeView($input,true,'header','footer');
        try{
        Helper::Admin()->isSuper();
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->log('a','b');
        }catch(\Exception $ex){}
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin  extends ComponentBase implements AdminActionInterface
{
    use GlobalAdminTrait;
    public $flag = true;
    public function localService()
    {
        return $this->flag ? MyAdminService::_():null;
    }
    public function id($check_login = true)
    {
        return 1;
    }
    public function name($check_login = true): string
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function login(array $post):array
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function logout(): void
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    
}
class MyAdminService implements AdminServiceInterface
{
    use SingletonTrait;
    
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        return;
    }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url =null): void
    {
        return;
    }    
    public function doIsSuper(int $admin_id): bool
    {
        return true;
    }
}