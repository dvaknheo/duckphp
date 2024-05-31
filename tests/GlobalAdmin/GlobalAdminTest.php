<?php
namespace tests\DuckPhp\GlobalAdmin;

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
        try{
            var_dump(Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::AdminId());
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::AdminName());
        }catch(\Exception $ex){}
        try{
        var_dump(Helper::AdminService());
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
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyAdmin extends GlobalAdmin
{
}
