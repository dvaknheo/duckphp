<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\BusinessHelper;
use DuckPhp\Helper\BusinessHelperTrait;

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
        BusinessHelper::Cache(new \stdClass);
        
        BusinessHelper::XpCall(function(){return "abc";});
        BusinessHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
        
            BusinessHelper::OnEvent("test",function(){});
            BusinessHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        try{
            //BusinessHelper::ThrowOn(true,"just a exception");
        }catch(\Exception $ex){
        }
        \LibCoverage\LibCoverage::End();
    }
}
