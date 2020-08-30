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
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        \DuckPhp\Core\Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        BusinessHelper::Setting($key);
        BusinessHelper::Config($key, $file_basename);
        BusinessHelper::LoadConfig($file_basename);
        
        \MyCodeCoverage::G()->end();
        /*
        BusinessHelper::G()->Setting($key);
        BusinessHelper::G()->Config($key, $file_basename='config');
        BusinessHelper::G()->LoadConfig($file_basename);
        //*/
    }
}
