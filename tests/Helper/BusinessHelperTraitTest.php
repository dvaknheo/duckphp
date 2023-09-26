<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\BusinessHelper;
use DuckPhp\Helper\BusinessHelperTrait;

class BusinessHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(BusinessHelperTrait::class);
        
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/BusinessHelper/';
        $options=[
            'path_config'=>$path_config,
        ];
        \DuckPhp\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        BusinessHelper::Setting($key);
        BusinessHelper::Config($file_basename, $key, null);

        BusinessHelper::Cache(new \stdClass);
        
        BusinessHelper::XpCall(function(){return "abc";});
        BusinessHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
        
            BusinessHelper::OnEvent("test",function(){});
            BusinessHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        try{
            BusinessHelper::ThrowOn(true,"just a exception");
        }catch(\Exception $ex){
        }
        \LibCoverage\LibCoverage::End();
    }
}
