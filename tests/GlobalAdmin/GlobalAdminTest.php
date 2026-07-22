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
        try{
            (Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        (Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        (Helper::AdminName());
        }catch(\Exception $ex){}
        try{
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->login([]);
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->logout();
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->checkAccess('class','method','url');
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->isSuper();
        }catch(\Exception $ex){}
        try{
        
        Helper::Admin()->urlForLogin();
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->urlForLogout();
        }catch(\Exception $ex){}
        try{
        Helper::Admin()->urlForHome();
        }catch(\Exception $ex){}
        try{
            Helper::Admin()->log("abc");
        }catch(\Exception $ex){}
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
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin extends GlobalAdmin
{
    public $flag = true;
    public function localService()
    {
        return $this->flag ? MyAdminService::_():null;
    }
    public function id($checkLogin =true):int
    {
        return 1;
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