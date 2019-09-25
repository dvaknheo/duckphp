<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\Configer;

class ConfigerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Configer::class);
        
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Core/Configer/';
        $options=[
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        Configer::G()->init($options);
        $key="key";
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, $file_basename='config');
        Configer::G()->_LoadConfig($file_basename='config');

        \MyCodeCoverage::G()->end(Configer::class);
        $this->assertTrue(true);
        /*
        Configer::G()->init($options=[], $context=null);
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, $file_basename='config');
        Configer::G()->_LoadConfig($file_basename='config');
        //*/
    }
}
