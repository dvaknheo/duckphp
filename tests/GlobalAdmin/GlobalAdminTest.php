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
        DuckPhp::_()->init(['class_admin'=>GlobalAdmin::class]);
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

        
        \LibCoverage\LibCoverage::End();
    }
}
