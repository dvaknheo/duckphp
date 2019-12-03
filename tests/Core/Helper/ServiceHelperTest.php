<?php
namespace tests\DuckPhp\Core\Helper;

use DuckPhp\Core\Helper\ServiceHelper;

class ServiceHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ServiceHelper::class);
        
        $path_base=realpath(__DIR__.'/../../');
        $path_config=$path_base.'/data_for_tests/Core/Helper/ServiceHelper/';
        $options=[
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        \DuckPhp\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        ServiceHelper::Setting($key);
        ServiceHelper::Config($key, $file_basename);
        ServiceHelper::LoadConfig($file_basename);
        
        \MyCodeCoverage::G()->end(ServiceHelper::class);
        $this->assertTrue(true);
        /*
        ServiceHelper::G()->Setting($key);
        ServiceHelper::G()->Config($key, $file_basename='config');
        ServiceHelper::G()->LoadConfig($file_basename);
        //*/
    }
}
