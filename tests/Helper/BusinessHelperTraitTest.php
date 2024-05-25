<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Core\App;

class BusinessHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(BusinessHelperTrait::class);
        

        $key='key';
        $file_basename='config';
        
        BusinessHelper::Setting($key);
        try{
        BusinessHelper::Config($file_basename, $key, null);
        }catch(\Exception $ex){}
        try{
        BusinessHelper::Cache(new \stdClass);
        }catch(\Exception $ex){}
        
        BusinessHelper::XpCall(function(){return "abc";});
        BusinessHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
        
            BusinessHelper::OnEvent("test",function(){});
            BusinessHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        try{
            BusinessHelper::BusinessThrowOn(false, "haha",1);
        }catch(\Throwable $ex){}
        
        try{
            BusinessHelper::AdminService();
        }catch(\Exception $ex){}
        try{
            BusinessHelper::UserService();
        }catch(\Exception $ex){}
        
        App::_()->init([]);
        BusinessHelper::PathOfRuntime();
        BusinessHelper::PathOfProject();

        
        \LibCoverage\LibCoverage::End();
    }
}
class BusinessHelper
{
    use BusinessHelperTrait;
}