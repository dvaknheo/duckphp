<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\Configer;

class ConfigerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Configer::class);
        
        $path_config=\GetClassTestPath(Configer::class);
        $options=[
            'path'=>$path_config,
            'path_config'=>$path_config,
            'skip_env_file'=>false,
        ];
        Configer::G()->init($options);
        Configer::G()->init($options);
        $key="key";
        Configer::G()->_Setting($key);
        Configer::G()->_Setting($key);
        Configer::G()->_Config($key, $file_basename='config');
        Configer::G()->_LoadConfig($file_basename='config');

        $options=[
            'path'=>dirname($path_config),
            'path_config'=>basename($path_config),
        ];
        Configer::G(new Configer)->init($options);
        Configer::G()->setConfig('XConfig',['a'=>'b']);
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
