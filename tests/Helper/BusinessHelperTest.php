<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\BusinessHelper;

class BusinessHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(BusinessHelper::class);
        
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/BusinessHelper/';
        $options=[
            'path_config'=>$path_config,
        ];
        \DuckPhp\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        BusinessHelper::Setting($key);
        BusinessHelper::Config($key, $file_basename);
        BusinessHelper::LoadConfig($file_basename);

        BusinessHelper::Cache(new \stdClass);
        
        BusinessHelper::XCall(function(){return "abc";});
        BusinessHelper::XCall(function(){ throw new \Exception('ex'); });
        
        try{
           BusinessHelper::Event();
        }catch(\Exception $ex){
        }
        try{
            BusinessHelper::OnEvent("test",null);
        }catch(\Exception $ex){
        }
        try{
            BusinessHelper::FireEvent("test",1,2,3);
        }catch(\Exception $ex){
        }

        \MyCodeCoverage::G()->end();
        /*
        BusinessHelper::G()->Setting($key);
        BusinessHelper::G()->Config($key, $file_basename='config');
        BusinessHelper::G()->LoadConfig($file_basename);
        //*/
    }
}
